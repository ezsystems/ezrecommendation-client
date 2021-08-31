<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Personalization\Storage;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content as ApiContent;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as ApiVersionInfo;
use eZ\Publish\API\Repository\Values\ContentType\ContentType as ApiContentType;
use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Storage\DataSourceInterface;
use Ibexa\Personalization\Content\DataResolverInterface;
use Ibexa\Personalization\Storage\ContentDataSource;
use Ibexa\Tests\Personalization\Creator\DataSourceTestItemCreator;
use Psr\Log\LoggerInterface;

abstract class AbstractContentDataSourceTestCase extends AbstractDataSourceTestCase
{
    protected const LANGUAGES_NAMES = [
        DataSourceTestItemCreator::LANGUAGE_EN => 'English',
        DataSourceTestItemCreator::LANGUAGE_DE => 'German',
        DataSourceTestItemCreator::LANGUAGE_FR => 'French',
        DataSourceTestItemCreator::LANGUAGE_NO => 'Norway',
    ];

    protected DataSourceInterface $contentDataSource;

    /** @var \eZ\Publish\API\Repository\SearchService|mixed|\PHPUnit\Framework\MockObject\MockObject */
    protected SearchService $searchService;

    /** @var \eZ\Publish\API\Repository\ContentService|mixed|\PHPUnit\Framework\MockObject\MockObject */
    protected ContentService $contentService;

    /** @var \eZ\Publish\Core\QueryType\QueryType|mixed|\PHPUnit\Framework\MockObject\MockObject */
    protected QueryType $queryType;

    /** @var \Ibexa\Personalization\Content\DataResolverInterface|mixed|\PHPUnit\Framework\MockObject\MockObject */
    protected DataResolverInterface $dataResolver;

    /** @var \Psr\Log\LoggerInterface|mixed|\PHPUnit\Framework\MockObject\MockObject */
    protected LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->searchService = $this->createMock(SearchService::class);
        $this->contentService = $this->createMock(ContentService::class);
        $this->queryType = $this->createMock(QueryType::class);
        $this->dataResolver = $this->createMock(DataResolverInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->contentDataSource = new ContentDataSource(
            $this->searchService,
            $this->contentService,
            $this->queryType,
            $this->dataResolver,
            $this->logger
        );
    }

    /**
     * @return array<\eZ\Publish\API\Repository\Values\Content\Search\SearchHit>
     */
    protected function createSearchHits(ApiContent ...$contents): array
    {
        $searchHits = [];

        foreach ($contents as $content) {
            $searchHits[] = $this->createSearchHit($content);
        }

        return $searchHits;
    }

    protected function createSearchHit(ApiContent $content): SearchHit
    {
        return new SearchHit(
            [
                'valueObject' => $content,
            ]
        );
    }

    protected function createContent(ApiContentType $contentType, ApiVersionInfo $versionInfo): ApiContent
    {
        return new Content(
            [
                'contentType' => $contentType,
                'versionInfo' => $versionInfo,
            ]
        );
    }

    /**
     * @param array<string, string> $names
     */
    protected function createContentType(
        int $contentTypeId,
        string $contentTypeIdentifier,
        string $language,
        array $names
    ): ApiContentType {
        return new ContentType(
            [
                'id' => $contentTypeId,
                'identifier' => $contentTypeIdentifier,
                'mainLanguageCode' => $language,
                'names' => $names,
            ]
        );
    }

    protected function createVersionInfo(ContentInfo $contentInfo): ApiVersionInfo
    {
        return new VersionInfo(
            [
                'contentInfo' => $contentInfo,
            ]
        );
    }

    protected function createContentInfo(int $contentId, string $mainLanguageCode, string $name): ContentInfo
    {
        return new ContentInfo(
            [
                'id' => $contentId,
                'mainLanguage' => new Language(
                    [
                        'id' => 1,
                        'languageCode' => $mainLanguageCode,
                        'name' => $name,
                    ]
                ),
            ]
        );
    }

    protected function getQuery(CriteriaInterface $criteria): Query
    {
        $query = new Query();
        $query->filter = new Query\Criterion\LogicalAnd(
            [
                new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE),
                new Query\Criterion\ContentTypeIdentifier($criteria->getItemTypeIdentifiers()),
            ]
        );

        return $query;
    }

    protected function configureQueryTypeToReturnQuery(Query $query, CriteriaInterface $criteria): void
    {
        $this->queryType
            ->expects(self::atLeastOnce())
            ->method('getQuery')
            ->with(['criteria' => $criteria])
            ->willReturn($query);
    }

    /**
     * @param array<\eZ\Publish\API\Repository\Values\Content\Search\SearchHit> $searchHits
     */
    protected function configureSearchServiceToReturnSearchResult(
        Query $query,
        CriteriaInterface $criteria,
        int $expectedCount,
        array $searchHits
    ): void {
        $this->searchService
            ->expects(self::atLeastOnce())
            ->method('findContent')
            ->with($query, ['languages' => $criteria->getLanguages()])
            ->willReturn(
                new SearchResult(
                    [
                        'totalCount' => $expectedCount,
                        'searchHits' => $searchHits,
                    ]
                )
            );
    }

    /**
     * @param array<array{\eZ\Publish\API\Repository\Values\Content\Content, array<string>}> $itemAttributesMap
     */
    protected function configureDataResolverToReturnItemAttributes(array $itemAttributesMap): void
    {
        if (!empty($itemAttributesMap)) {
            $this->dataResolver
                ->expects(self::atLeastOnce())
                ->method('resolve')
                ->willReturnMap($itemAttributesMap);
        }
    }

    /**
     * @param array<string> $languages
     */
    protected function configureContentServiceToReturnContent(
        string $itemId,
        array $languages,
        ApiContent $content
    ): void {
        $this->contentService
            ->expects(self::once())
            ->method('loadContent')
            ->with($itemId, $languages)
            ->willReturn($content);
    }
}
