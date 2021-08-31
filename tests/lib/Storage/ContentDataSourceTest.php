<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Personalization\Storage;

use eZ\Publish\API\Repository\Values\Content\Content as ApiContent;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use EzSystems\EzRecommendationClient\Exception\ItemNotFoundException;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;
use Ibexa\Personalization\Value\Storage\ItemList;
use Ibexa\Tests\Personalization\Creator\DataSourceTestItemCreator;

/**
 * @covers \Ibexa\Personalization\Storage\ContentDataSource
 */
final class ContentDataSourceTest extends AbstractContentDataSourceTestCase
{
    /**
     * @param array<\eZ\Publish\API\Repository\Values\Content\Search\SearchHit> $searchHits
     *
     * @dataProvider provideDataForTestCountItems
     */
    public function testCountItems(
        CriteriaInterface $criteria,
        int $expectedCount,
        array $searchHits
    ): void {
        $query = $this->getQuery($criteria);
        $query->limit = 0;
        $this->configureQueryTypeToReturnQuery($query, $criteria);
        $this->configureSearchServiceToReturnSearchResult($query, $criteria, $expectedCount, $searchHits);

        self::assertEquals(
            $expectedCount,
            $this->contentDataSource->countItems($criteria)
        );
        self::assertCount(
            $expectedCount,
            $this->contentDataSource->fetchItems($criteria)
        );
    }

    public function testFetchItemsReturnEmptyListWhenNotFoundExceptionIsThrown(): void
    {
        $undefinedLanguage = DataSourceTestItemCreator::LANGUAGE_DE;
        $criteria = $this->itemCreator->createTestCriteria(
            [DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER],
            [$undefinedLanguage, DataSourceTestItemCreator::LANGUAGE_EN]
        );
        $query = $this->getQuery($criteria);
        $query->limit = $criteria->getLimit();
        $this->configureQueryTypeToReturnQuery($query, $criteria);
        $this->searchService
            ->expects(self::atLeastOnce())
            ->method('findContent')
            ->with($query, ['languages' => $criteria->getLanguages()])
            ->willThrowException(new NotFoundException('language', $undefinedLanguage));

        self::assertEquals(
            $this->itemCreator->createTestItemList(),
            $this->contentDataSource->fetchItems($criteria)
        );
    }

    /**
     * @param array<\eZ\Publish\API\Repository\Values\Content\Search\SearchHit> $searchHits
     * @param array<array{\eZ\Publish\API\Repository\Values\Content\Content, array<string>}> $contentFieldsMap
     *
     * @dataProvider provideDataForTestFetchItems
     */
    public function testFetchItems(
        CriteriaInterface $criteria,
        ItemListInterface $expectedItems,
        array $searchHits,
        array $contentFieldsMap
    ): void {
        $query = $this->getQuery($criteria);
        $query->limit = $criteria->getLimit();
        $this->configureQueryTypeToReturnQuery($query, $criteria);
        $this->configureSearchServiceToReturnSearchResult($query, $criteria, $expectedItems->count(), $searchHits);
        $this->configureDataResolverToReturnItemAttributes($contentFieldsMap);

        self::assertEquals(
            $expectedItems,
            $this->contentDataSource->fetchItems($criteria)
        );
    }

    public function testFetchItemThrowItemNotFoundException(): void
    {
        $itemId = '10';
        $language = 'pl';

        $this->expectException(ItemNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Item not found with id: %s and language: %s', $itemId, $language));

        $this->contentService
            ->expects(self::once())
            ->method('loadContent')
            ->with($itemId, [$language])
            ->willThrowException(new NotFoundException('content', $itemId));

        $this->contentDataSource->fetchItem($itemId, $language);
    }

    public function testFetchItem(): void
    {
        $itemId = '10';
        $language = DataSourceTestItemCreator::LANGUAGE_EN;
        $article = $this->createContentArticle((int)$itemId, $language);
        $this->configureDataResolverToReturnItemAttributes(
            [
                [
                    $article,
                    $this->itemCreator->createTestItemAttributes(
                        1,
                        DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
                        DataSourceTestItemCreator::ARTICLE_NAME,
                        DataSourceTestItemCreator::LANGUAGE_EN
                    ),
                ],
            ]
        );
        $this->configureContentServiceToReturnContent($itemId, [$language], $article);

        self::assertEquals(
            $this->itemCreator->createTestItem(
                1,
                $itemId,
                DataSourceTestItemCreator::ARTICLE_TYPE_ID,
                DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
                DataSourceTestItemCreator::ARTICLE_TYPE_NAME,
                DataSourceTestItemCreator::LANGUAGE_EN,
            ),
            $this->contentDataSource->fetchItem($itemId, $language)
        );
    }

    /**
     * @phpstan-return iterable<array{
     *  \Ibexa\Contracts\Personalization\Criteria\CriteriaInterface,
     *  int,
     *  array<\eZ\Publish\API\Repository\Values\Content\Search\SearchHit>
     * }>
     */
    public function provideDataForTestCountItems(): iterable
    {
        $criteria = $this->itemCreator->createTestCriteria(
            [DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER, DataSourceTestItemCreator::PRODUCT_TYPE_IDENTIFIER],
            [DataSourceTestItemCreator::LANGUAGE_EN, DataSourceTestItemCreator::LANGUAGE_DE]
        );

        yield [$criteria, 0, $this->createSearchHits()];
        yield [
            $criteria,
            3,
            $this->createSearchHits(
                $this->createContentArticle(1, DataSourceTestItemCreator::LANGUAGE_EN),
                $this->createContentArticle(2, DataSourceTestItemCreator::LANGUAGE_DE),
                $this->createContentProduct(3, DataSourceTestItemCreator::LANGUAGE_EN),
            ),
        ];
    }

    /**
     * @phpstan-return iterable<array{
     *  \Ibexa\Contracts\Personalization\Criteria\CriteriaInterface,
     *  \Ibexa\Contracts\Personalization\Value\ItemListInterface,
     *  array<\eZ\Publish\API\Repository\Values\Content\Search\SearchHit>,
     *  array<array{\eZ\Publish\API\Repository\Values\Content\Content, array<string>}>
     * }>
     */
    public function provideDataForTestFetchItems(): iterable
    {
        $criteria = $this->itemCreator->createTestCriteria(
            [DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER, DataSourceTestItemCreator::PRODUCT_TYPE_IDENTIFIER],
            [DataSourceTestItemCreator::LANGUAGE_EN, DataSourceTestItemCreator::LANGUAGE_DE]
        );

        $articleEn = $this->createContentArticle(1, DataSourceTestItemCreator::LANGUAGE_EN);
        $articleDe = $this->createContentArticle(2, DataSourceTestItemCreator::LANGUAGE_DE);
        $productEn = $this->createContentProduct(3, DataSourceTestItemCreator::LANGUAGE_EN);

        yield [$criteria, new ItemList([]), [], []];
        yield [
            $criteria,
            $this->itemCreator->createTestItemList(
                $this->itemCreator->createTestItem(
                    1,
                    '1',
                    DataSourceTestItemCreator::ARTICLE_TYPE_ID,
                    DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
                    DataSourceTestItemCreator::ARTICLE_TYPE_NAME,
                    DataSourceTestItemCreator::LANGUAGE_EN,
                ),
                $this->itemCreator->createTestItem(
                    2,
                    '2',
                    DataSourceTestItemCreator::ARTICLE_TYPE_ID,
                    DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
                    DataSourceTestItemCreator::ARTICLE_TYPE_NAME,
                    DataSourceTestItemCreator::LANGUAGE_DE,
                ),
                $this->itemCreator->createTestItem(
                    1,
                    '3',
                    DataSourceTestItemCreator::PRODUCT_TYPE_ID,
                    DataSourceTestItemCreator::PRODUCT_TYPE_IDENTIFIER,
                    DataSourceTestItemCreator::PRODUCT_TYPE_NAME,
                    DataSourceTestItemCreator::LANGUAGE_EN,
                ),
            ),
            $this->createSearchHits($articleEn, $articleDe, $productEn),
            [
                [
                    $articleEn,
                    $this->itemCreator->createTestItemAttributes(
                        1,
                        DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
                        DataSourceTestItemCreator::ARTICLE_NAME,
                        DataSourceTestItemCreator::LANGUAGE_EN
                    ),
                ],
                [
                    $articleDe,
                    $this->itemCreator->createTestItemAttributes(
                        2,
                        DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
                        DataSourceTestItemCreator::ARTICLE_NAME,
                        DataSourceTestItemCreator::LANGUAGE_DE
                    ),
                ],
                [
                    $productEn,
                    $this->itemCreator->createTestItemAttributes(
                        1,
                        DataSourceTestItemCreator::PRODUCT_TYPE_IDENTIFIER,
                        DataSourceTestItemCreator::PRODUCT_NAME,
                        DataSourceTestItemCreator::LANGUAGE_EN
                    ),
                ],
            ],
        ];
    }

    private function createContentArticle(int $contentId, string $language): ApiContent
    {
        $articleContentType = $this->createContentType(
            DataSourceTestItemCreator::ARTICLE_TYPE_ID,
            DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
            $language,
            [
                $language => DataSourceTestItemCreator::ARTICLE_NAME,
            ]
        );
        $articleContentInfo = $this->createContentInfo(
            $contentId,
            $language,
            self::LANGUAGES_NAMES[$language]
        );

        return $this->createContent(
            $articleContentType,
            $this->createVersionInfo($articleContentInfo)
        );
    }

    private function createContentProduct(int $contentId, string $language): ApiContent
    {
        $articleContentType = $this->createContentType(
            DataSourceTestItemCreator::PRODUCT_TYPE_ID,
            DataSourceTestItemCreator::PRODUCT_TYPE_IDENTIFIER,
            $language,
            [
                $language => DataSourceTestItemCreator::PRODUCT_TYPE_NAME,
            ]
        );
        $articleContentInfo = $this->createContentInfo(
            $contentId,
            DataSourceTestItemCreator::LANGUAGE_EN,
            self::LANGUAGES_NAMES[$language]
        );

        return $this->createContent(
            $articleContentType,
            $this->createVersionInfo($articleContentInfo)
        );
    }
}
