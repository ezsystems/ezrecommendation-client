<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exporter;

use Exception;
use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\REST\Common\Output\Generator;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface;
use EzSystems\EzRecommendationClient\Content\Content;
use EzSystems\EzRecommendationClient\Generator\ContentListElementGenerator;
use EzSystems\EzRecommendationClient\Helper\FileSystemHelper;
use EzSystems\EzRecommendationClient\Helper\ParamsConverterHelper;
use EzSystems\EzRecommendationClient\Helper\SiteAccessHelper;
use EzSystems\EzRecommendationClient\Request\ExportNotifierRequest;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use EzSystems\EzRecommendationClient\Value\ContentData;
use EzSystems\EzRecommendationClient\Value\Notification;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Generates and export content to Recommendation Server.
 */
class Exporter implements ExporterInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \eZ\Publish\Api\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\Api\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \eZ\Publish\Api\Repository\LocationService */
    private $locationService;

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface */
    private $credentialsChecker;

    /** @var \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface */
    private $client;

    /** @var \EzSystems\EzRecommendationClient\Helper\FileSystemHelper */
    private $fileSystemHelper;

    /** @var \EzSystems\EzRecommendationClient\Helper\SiteAccessHelper */
    private $siteAccessHelper;

    /** @var \EzSystems\EzRecommendationClient\Content\Content */
    private $content;

    /** @var \EzSystems\EzRecommendationClient\Generator\ContentListElementGenerator */
    private $contentListElementGenerator;

    /** @var \eZ\Publish\Core\REST\Common\Output\Generator */
    private $outputGenerator;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(
        Repository $repository,
        SearchServiceInterface $searchService,
        ContentTypeServiceInterface $contentTypeService,
        LocationServiceInterface $locationService,
        EzRecommendationClientInterface $client,
        CredentialsCheckerInterface $credentialsChecker,
        FileSystemHelper $fileSystemHelper,
        SiteAccessHelper $siteAccessHelper,
        Content $content,
        ContentListElementGenerator $contentListElementGenerator,
        Generator $outputGenerator,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->searchService = $searchService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->client = $client;
        $this->credentialsChecker = $credentialsChecker;
        $this->fileSystemHelper = $fileSystemHelper;
        $this->siteAccessHelper = $siteAccessHelper;
        $this->content = $content;
        $this->contentListElementGenerator = $contentListElementGenerator;
        $this->outputGenerator = $outputGenerator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function runExport(array $options, OutputInterface $output): void
    {
        $options = $this->validate($options);

        $options['contentTypeIds'] = ParamsConverterHelper::getIdListFromString($options['contentTypeIdList']);
        $chunkDir = $this->fileSystemHelper->createChunkDir();

        $languages = $this->getLanguages($options);
        $options['languages'] = $languages;

        try {
            $this->fileSystemHelper->lock();
            $urls = $this->generateFiles($languages, $chunkDir, $options, $output);
            $this->fileSystemHelper->unlock();

            /** @var ExportCredentials $credentials */
            $credentials = $this->credentialsChecker->getCredentials();
            $securedDirCredentials = $this->fileSystemHelper->secureDir($chunkDir, $credentials);

            $notification = $this->getNotification($options, $urls, $securedDirCredentials);
            $response = $this->client->notifier()->notify($notification);

            $this->logger->info(sprintf('eZ Recommendation Response: %s', $response->getBody()));
            $output->writeln('Done');
        } catch (Exception $e) {
            $this->logger->error(sprintf('Error while generating export: %s', $e->getMessage()));
            $this->fileSystemHelper->unlock();

            throw $e;
        }
    }

    /**
     * Validates required options.
     *
     * @throws Exception
     */
    private function validate(array $options): array
    {
        if (\array_key_exists('mandatorId', $options)) {
            $options['mandatorId'] = (int) $options['mandatorId'];
        }

        list($customerId, $licenseKey) =
            $this->siteAccessHelper->getRecommendationServiceCredentials($options['mandatorId'], $options['siteaccess']);

        $options = array_filter($options, function ($val) {
            return $val !== null;
        });

        $resolver = new OptionsResolver();
        $resolver->setRequired(['contentTypeIdList', 'host', 'webHook', 'transaction']);
        $resolver->setDefined(array_keys($options));
        $resolver->setDefaults([
            'transaction' => (new \DateTime())->format('YmdHis') . rand(111, 999),
            'customerId' => $customerId,
            'licenseKey' => $licenseKey,
            'mandatorId' => null,
            'siteaccess' => null,
            'lang' => null,
        ]);

        return $resolver->resolve($options);
    }

    /**
     * Returns languages list.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    private function getLanguages(array $options): array
    {
        if (!empty($options['lang'])) {
            return ParamsConverterHelper::getArrayFromString($options['lang']);
        }

        return $this->siteAccessHelper->getLanguages($options['mandatorId'], $options['siteaccess']);
    }

    /**
     * Generate export files.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function generateFiles(array $languages, string $chunkDir, array $options, OutputInterface $output): array
    {
        $urls = [];

        $output->writeln(sprintf('Exporting %s content types', \count($options['contentTypeIds'])));

        foreach ($options['contentTypeIds'] as $id) {
            $contentTypeId = (int) $id;
            $contentTypeCurrentName = null;
            $contentType = $this->contentTypeService->loadContentType($contentTypeId);

            foreach ($languages as $lang) {
                $options['lang'] = $lang;

                $count = $this->countContentItemsByContentTypeId($contentTypeId, $options);

                $info = sprintf('Fetching %s items of contentTypeId %s (language: %s)', $count, $contentTypeId, $lang);
                $output->writeln($info);
                $this->logger->info($info);

                for ($i = 1; $i <= ceil($count / $options['pageSize']); ++$i) {
                    $filename = sprintf('%d_%s_%d', $contentTypeId, $lang, $i);
                    $chunkPath = $chunkDir . $filename;
                    $options['page'] = $i;

                    $output->writeln(sprintf(
                        'Fetching content from database for contentTypeId: %s, language: %s, chunk: #%s',
                        $contentTypeId,
                        $lang,
                        $i
                    ));

                    $contentItems = $this->getContentItems($contentTypeId, $options);
                    $parameters = new ParameterBag($options);

                    $output->writeln(sprintf(
                        'Preparing content for contentTypeId: %s, language: %s, amount: %s, chunk: #%s',
                        $contentTypeId,
                        $lang,
                        \count($contentItems),
                        $i
                    ));

                    $content = $this->repository->sudo(
                        function () use ($contentTypeId, $contentItems, $parameters, $output) {
                            return $this->content->prepareContent([$contentTypeId => $contentItems], $parameters, $output);
                        }
                    );

                    unset($contentItems);

                    $output->writeln(sprintf(
                        'Generating file for contentTypeId: %s, language: %s, chunk: #%s',
                        $contentTypeId,
                        $lang,
                        $i
                    ));

                    $this->generateFile($content, $chunkPath, $options);

                    unset($content);

                    $url = sprintf(
                        '%s/api/ezp/v2/ez_recommendation/v1/exportDownload/%s%s',
                        $options['host'], $chunkDir, $filename
                    );

                    $info = sprintf('Generating url: %s', $url);
                    $output->writeln($info);
                    $this->logger->info($info);

                    $urls[$contentTypeId][$lang]['urlList'][] = $url;
                    $urls[$contentTypeId][$lang]['contentTypeName'] = $contentType->getName($lang) ?? $contentType->getName($contentType->mainLanguageCode);
                }
            }
        }

        return $urls;
    }

    /**
     * Returns total amount of content based on ContentType ids.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function countContentItemsByContentTypeId(int $contentTypeId, array $options): ?int
    {
        $query = $this->getQuery($contentTypeId, $options);
        $query->limit = 0;

        return $this->repository->sudo(function () use ($query, $options) {
            return $this->searchService->findContent(
                $query,
                (!empty($options['lang']) ? array('languages' => array($options['lang'])) : array())
            )->totalCount;
        });
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getContentItems(int $contentTypeId, array $options): array
    {
        $query = $this->getQuery($contentTypeId, $options);
        $query->limit = (int) $options['pageSize'];
        $query->offset = $options['page'] * $options['pageSize'] - $options['pageSize'];

        return $this->repository->sudo(function () use ($query, $options) {
            return $this->searchService->findContent(
                $query,
                (!empty($options['lang']) ? array('languages' => array($options['lang'])) : array())
            )->searchHits;
        });
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getQuery(int $contentTypeId, array $options): Query
    {
        $criteria = [
            new Criterion\ContentTypeId($contentTypeId),
        ];

        if ($options['path']) {
            $criteria[] = new Criterion\Subtree($options['path']);
        }

        if (!$options['hidden']) {
            $criteria[] = new Criterion\Visibility(Criterion\Visibility::VISIBLE);
        }

        $criteria[] = $this->generateSubtreeCriteria($options['mandatorId'], $options['siteaccess']);

        $query = new Query();
        $query->query = new Criterion\LogicalAnd($criteria);

        return $query;
    }

    /**
     * Generates Criterions based on mandatoryId or requested siteAccess.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function generateSubtreeCriteria(?int $mandatorId, ?string $siteAccess): Criterion\LogicalOr
    {
        $siteAccesses = $this->siteAccessHelper->getSiteAccesses($mandatorId, $siteAccess);

        $subtreeCriteria = [];
        $rootLocations = $this->siteAccessHelper->getRootLocationsBySiteAccesses($siteAccesses);
        foreach ($rootLocations as $rootLocationId) {
            $subtreeCriteria[] = $this->repository->sudo(function () use ($rootLocationId) {
                return new Criterion\Subtree($this->locationService->loadLocation($rootLocationId)->pathString);
            });
        }

        return new Criterion\LogicalOr($subtreeCriteria);
    }

    private function generateFile(array $content, string $chunkPath, array $options): void
    {
        $data = new ContentData($content, $options);

        $this->outputGenerator->reset();
        $this->outputGenerator->startDocument($data);

        $contents = array();
        foreach ($data->contents as $contentTypes) {
            foreach ($contentTypes as $contentType) {
                $contents[] = $contentType;
            }
        }

        $this->contentListElementGenerator->generateElement($this->outputGenerator, $contents);

        unset($contents);

        $filePath = $this->fileSystemHelper->getDir() . $chunkPath;
        $this->fileSystemHelper->save($filePath, $this->outputGenerator->endDocument($data));

        unset($data);

        $this->logger->info(sprintf('Generating file: %s', $filePath));
    }

    private function getNotification(array $options, array $urls, array $securedDirCredentials): Notification
    {
        $notfication = new Notification();
        $notfication->events = $this->getNotificationEvents($urls, $securedDirCredentials);
        $notfication->licenseKey = $options['licenseKey'];
        $notfication->customerId = (int) $options['customerId'];
        $notfication->transaction = $options['transaction'];
        $notfication->endPointUri = $options['webHook'];

        return $notfication;
    }

    private function getNotificationEvents(array $urls, array $securedDirCredentials): array
    {
        $notifications = [];

        foreach ($urls as $contentTypeId => $languages) {
            foreach ($languages as $lang => $contentTypeInfo) {
                $notification = new ExportNotifierRequest([
                    ExportNotifierRequest::ACTION_KEY => 'FULL',
                    ExportNotifierRequest::FORMAT_KEY => 'EZ',
                    ExportNotifierRequest::CONTENT_TYPE_ID_KEY => $contentTypeId,
                    ExportNotifierRequest::CONTENT_TYPE_NAME_KEY => $contentTypeInfo['contentTypeName'],
                    ExportNotifierRequest::LANG_KEY => $lang,
                    ExportNotifierRequest::URI_KEY => $contentTypeInfo['urlList'],
                    ExportNotifierRequest::CREDENTIALS_KEY => $securedDirCredentials ?? null,
                ]);

                $notifications[] = $notification->getRequestAttributes();
            }
        }

        return $notifications;
    }
}
