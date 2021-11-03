<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exporter;

use Exception;
use EzSystems\EzRecommendationClient\File\FileManagerInterface;
use EzSystems\EzRecommendationClient\Service\ExportNotificationServiceInterface;
use EzSystems\EzRecommendationClient\Value\Export\EventList;
use EzSystems\EzRecommendationClient\Value\ExportMethod;
use Ibexa\Contracts\PersonalizationClient\Criteria\CriteriaInterface;
use Ibexa\Contracts\PersonalizationClient\Value\ItemGroupInterface;
use Ibexa\PersonalizationClient\Config\ItemType\IncludedItemTypeResolverInterface;
use Ibexa\PersonalizationClient\Criteria\Criteria;
use Ibexa\PersonalizationClient\Generator\File\ExportFileGeneratorInterface;
use Ibexa\PersonalizationClient\Service\Export\ExportServiceInterface;
use Ibexa\PersonalizationClient\Service\Storage\DataSourceServiceInterface;
use Ibexa\PersonalizationClient\Strategy\Storage\SupportedGroupItemStrategy;
use Ibexa\PersonalizationClient\Value\Export\Credentials;
use Ibexa\PersonalizationClient\Value\Export\Event;
use Ibexa\PersonalizationClient\Value\Export\FileSettings;
use Ibexa\PersonalizationClient\Value\Export\Parameters;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Generates and export content to Recommendation Server.
 */
final class Exporter implements ExporterInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const API_ENDPOINT_URL = '%s/api/ezp/v2/ez_recommendation/v1/exportDownload/%s';
    private const FILE_FORMAT_NAME = '%s_%s_%d';

    private DataSourceServiceInterface $dataSourceService;

    private ExportServiceInterface $exportService;

    private IncludedItemTypeResolverInterface $itemTypeResolver;

    private ExportFileGeneratorInterface $exportFileGenerator;

    private FileManagerInterface $fileManager;

    private ExportNotificationServiceInterface $notificationService;

    public function __construct(
        DataSourceServiceInterface $dataSourceService,
        ExportServiceInterface $exportService,
        IncludedItemTypeResolverInterface $itemTypeResolver,
        ExportFileGeneratorInterface $exportFileGenerator,
        FileManagerInterface $fileManager,
        ExportNotificationServiceInterface $notificationService
    ) {
        $this->dataSourceService = $dataSourceService;
        $this->exportService = $exportService;
        $this->itemTypeResolver = $itemTypeResolver;
        $this->exportFileGenerator = $exportFileGenerator;
        $this->fileManager = $fileManager;
        $this->notificationService = $notificationService;
        $this->logger = new NullLogger();
    }

    /**
     * @throws \Exception
     */
    public function export(Parameters $parameters): void
    {
        try {
            $events = $this->getExportEvents($parameters);

            $this->logger->info(sprintf('Exporting %s item types', $events->countItemTypes()));

            $response = $this->notificationService->sendNotification($parameters, $events);

            if (null === $response) {
                $this->logger->info('Ibexa Recommendation Response: no response');

                return;
            }

            $this->logger->info(sprintf('Ibexa Recommendation Response: %s', $response->getBody()));
        } catch (Exception $e) {
            $this->logger->error(sprintf('Error during export: %s', $e->getMessage()));

            throw $e;
        }
    }

    public function hasExportItems(Parameters $parameters): bool
    {
        return $this->dataSourceService->getItems($this->createCriteria($parameters, false))->count() > 0;
    }

    public function getExportEvents(Parameters $parameters): EventList
    {
        $this->fileManager->lock();

        $siteAccess = $parameters->getSiteaccess();
        $chunkDir = $this->fileManager->createChunkDir();
        $credentials = $this->exportService->getCredentials($siteAccess);
        $generatedEvents = [];

        if (ExportMethod::BASIC === $this->exportService->getAuthenticationMethod($siteAccess)) {
            $this->secureDir($chunkDir, $credentials);
        }

        $this->logger->info('Fetching items from data sources');

        $groupedItems = $this->dataSourceService->getGroupedItems(
            $this->createCriteria($parameters, true),
            SupportedGroupItemStrategy::GROUP_BY_ITEM_TYPE_AND_LANGUAGE
        );

        foreach ($groupedItems->getGroups() as $group) {
            $generatedEvents[] = $this->createEvent($group, $chunkDir, $parameters, $credentials);
        }

        $this->fileManager->unlock();

        return new EventList($generatedEvents);
    }

    private function secureDir(string $chunkDir, Credentials $credentials): void
    {
        [$login, $password] = [$credentials->getLogin(), $credentials->getPassword()];

        if (isset($login, $password)) {
            $this->fileManager->secureDir($chunkDir, $login, $password);
        }
    }

    private function createCriteria(Parameters $parameters, bool $useLogger): CriteriaInterface
    {
        return new Criteria(
            $this->itemTypeResolver->resolve(
                $parameters->getItemTypeIdentifierList(),
                $useLogger,
                $parameters->getSiteaccess()
            ),
            $parameters->getLanguages(),
            $parameters->getPageSize()
        );
    }

    private function createEvent(
        ItemGroupInterface $itemGroup,
        string $chunkDir,
        Parameters $parameters,
        Credentials $credentials
    ): Event {
        $generatedUrls = [];
        $items = $itemGroup->getItems();
        $itemType = $items->first()->getType();
        [$identifier, $language] = explode('_', $itemGroup->getIdentifier());
        $count = $items->count();
        $pageSize = $parameters->getPageSize();
        $length = ceil($count / $pageSize);
        $this->logger->info(
            sprintf(
                'Fetching %s items of item type: identifier %s (language: %s)',
                $count,
                $identifier,
                $language
            )
        );

        for ($i = 1; $i <= $length; ++$i) {
            $page = $i;
            $offset = $page * $pageSize - $pageSize;
            $filename = sprintf(self::FILE_FORMAT_NAME, $identifier, $language, $page);
            $chunkPath = $chunkDir . $filename;
            $exportFileSettings = new FileSettings(
                $items->slice($offset, $pageSize),
                $identifier,
                $language,
                $page,
                $chunkPath
            );

            $this->generateExportFile($exportFileSettings);
            $generatedUrls[] = $this->generateFileUrl($parameters->getHost(), $chunkPath);
        }

        $this->checkItemTypeIdentifiers($identifier, $items);

        return new Event(
            $itemType->getId(),
            $itemType->getName(),
            $language,
            $generatedUrls,
            $credentials
        );
    }

    private function generateExportFile(FileSettings $exportFileSettings): void
    {
        $this->logger->info(sprintf(
            'Generating file for item type identifier: %s, language: %s, chunk: #%s',
            $exportFileSettings->getIdentifier(),
            $exportFileSettings->getLanguage(),
            $exportFileSettings->getChunkPath()
        ));

        $this->exportFileGenerator->generate($exportFileSettings);
    }

    private function generateFileUrl(string $host, string $chunkPath): string
    {
        $url = sprintf(self::API_ENDPOINT_URL, $host, $chunkPath);
        $this->logger->info(sprintf('Generating url: %s', $url));

        return $url;
    }

    /**
     * @param iterable<\Ibexa\Contracts\PersonalizationClient\Value\ItemInterface> $items
     */
    private function checkItemTypeIdentifiers(string $groupItemTypeIdentifier, iterable $items): void
    {
        foreach ($items as $item) {
            $itemTypeIdentifier = $item->getType()->getIdentifier();

            if ($groupItemTypeIdentifier !== $itemTypeIdentifier) {
                $this->logger->info(sprintf(
                    'Item: %s has different item type identifier: %s than group item type %s',
                    $item->getId(),
                    $itemTypeIdentifier,
                    $groupItemTypeIdentifier
                ));
            }
        }
    }
}
