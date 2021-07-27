<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exporter;

use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\Repository;
use EzSystems\EzRecommendationClient\File\ExportFileGenerator;
use EzSystems\EzRecommendationClient\Helper\ContentHelper;
use EzSystems\EzRecommendationClient\Service\ContentServiceInterface;
use Ibexa\Personalization\Value\Export\Parameters;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates and export content to Recommendation Server.
 */
final class Exporter implements ExporterInterface
{
    private const API_ENDPOINT_URL = '%s/api/ezp/v2/ez_recommendation/v1/exportDownload/%s';

    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \EzSystems\EzRecommendationClient\File\ExportFileGenerator */
    private $exportFileGenerator;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \EzSystems\EzRecommendationClient\Service\ContentServiceInterface */
    private $contentService;

    /** @var \EzSystems\EzRecommendationClient\Helper\ContentHelper */
    private $contentHelper;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(
        Repository $repository,
        ExportFileGenerator $exportFileGenerator,
        ContentTypeServiceInterface $contentTypeService,
        ContentServiceInterface $contentService,
        ContentHelper $contentHelper,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->exportFileGenerator = $exportFileGenerator;
        $this->contentTypeService = $contentTypeService;
        $this->contentService = $contentService;
        $this->contentHelper = $contentHelper;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function run(Parameters $parameters, string $chunkDir, OutputInterface $output): array
    {
        $urls = [];

        $output->writeln(sprintf('Exporting %s content types', \count($parameters->getItemTypeIdentifierList())));

        foreach ($parameters->getItemTypeIdentifierList() as $id) {
            $contentTypeId = (int)$id;
            $urls[$contentTypeId] = $this->getContentForGivenLanguages($contentTypeId, $chunkDir, $parameters, $output);
        }

        return $urls;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getContentForGivenLanguages(
        int $contentTypeId,
        string $chunkDir,
        Parameters $parameters,
        OutputInterface $output): array
    {
        $contents = [];

        foreach ($parameters->languages as $lang) {
            $parameters->lang = $lang;

            $options = [
                'lang' => $lang,
                'languages' => $parameters->getLanguages(),
                'pageSize' => $parameters->getPageSize(),
                'customerId' => $parameters->getCustomerId(),
                'siteaccess' => $parameters->getSiteaccess(),
            ];
            $count = $this->contentHelper->countContentItemsByContentTypeId($contentTypeId, $options);

            $info = sprintf('Fetching %s items of contentTypeId %s (language: %s)', $count, $contentTypeId, $parameters->lang);
            $output->writeln($info);
            $this->logger->info($info);

            for ($i = 1; $i <= ceil($count / $parameters->pageSize); ++$i) {
                $filename = sprintf('%d_%s_%d', $contentTypeId, $lang, $i);
                $chunkPath = $chunkDir . $filename;
                $parameters->page = $i;

                $this->generateFileForContentType($contentTypeId, $chunkPath, $parameters, $output);

                $contents[$lang] = $this->generateUrlList(
                    $contentTypeId,
                    $parameters->lang,
                    $this->generateUrl($parameters->host, $chunkPath, $output)
                );
            }
        }

        return $contents;
    }

    private function generateFileForContentType(
        int $contentTypeId,
        string $chunkPath,
        Parameters $parameters,
        OutputInterface $output): void
    {
        $content = $this->repository->sudo(
            function () use ($contentTypeId, $parameters, $output) {
                return $this->contentService->fetchContent($contentTypeId, $parameters, $output);
            }
        );

        $output->writeln(sprintf(
            'Generating file for contentTypeId: %s, language: %s, chunk: #%s',
            $contentTypeId,
            $parameters->lang,
            $parameters->page
        ));

        $this->exportFileGenerator->generateFile($content, $chunkPath, $parameters->getProperties());

        unset($content);
    }

    private function generateUrl(string $host, string $chunkPath, OutputInterface $output): string
    {
        $url = sprintf(
            self::API_ENDPOINT_URL,
            $host, $chunkPath
        );

        $info = sprintf('Generating url: %s', $url);
        $output->writeln($info);
        $this->logger->info($info);

        return $url;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function generateUrlList(int $contentTypeId, string $lang, string $url): array
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);

        return [
            'urlList' => [$url],
            'contentTypeName' => $contentType->getName($lang) ?? $contentType->getName($contentType->mainLanguageCode),
        ];
    }
}
