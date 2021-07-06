<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Service\Storage;

use EzSystems\EzRecommendationClient\Service\Storage\DataSourceService;
use EzSystems\EzRecommendationClient\Strategy\Storage\ItemGroupListStrategyInterface;
use EzSystems\EzRecommendationClient\Strategy\Storage\SupportedGroupItemStrategy;
use EzSystems\EzRecommendationClient\Tests\Storage\AbstractDataSourceTestCase;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Storage\DataSourceInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;

final class DataSourceServiceTest extends AbstractDataSourceTestCase
{
    private ItemGroupListStrategyInterface $itemGroupStrategy;

    public function setUp(): void
    {
        $this->itemGroupStrategy = $this->createMock(ItemGroupListStrategyInterface::class);
    }

    public function testGetItem(): void
    {
        $item = $this->itemCreator->createTestItem(
            1,
            '1',
            ItemType::ARTICLE_IDENTIFIER,
            ItemType::ARTICLE_NAME,
            'en'
        );

        $dataSourceService = new DataSourceService(
            [
                $this->createDataSourceMockForGetItem(
                    '1',
                    'en',
                    $item
                ),
            ],
            $this->itemGroupStrategy
        );

        self::assertEquals(
            $item,
            $dataSourceService->getItem('1', 'en')
        );
    }

    /**
     * @dataProvider providerForTestGetItems
     */
    public function testGetItems(
        CriteriaInterface $criteria,
        ItemListInterface $expectedItems
    ): void {
        $inMemoryDataSource = $this->createDataSourceMockForCriteria(
            $criteria,
            $expectedItems,
            count($expectedItems)
        );

        $dataSourceService = new DataSourceService(
            [
                $inMemoryDataSource,
            ],
            $this->itemGroupStrategy
        );

        self::assertEquals(
            $expectedItems,
            $dataSourceService->getItems($criteria)
        );
    }

    public function testGetItemsFromMultipleDataSources(): void
    {
        $criteria = $this->itemCreator->createTestCriteria(
            [
                ItemType::ARTICLE_IDENTIFIER,
                ItemType::PRODUCT_IDENTIFIER,
            ],
            ['en']
        );
        $products = $this->itemCreator->createTestItemListForEnglishProducts();
        $articles = $this->itemCreator->createTestItemListForEnglishArticles();
        $productsDataSource = $this->createDataSourceMockForCriteria(
            $criteria,
            $products,
            count($products)
        );
        $articlesDataSource = $this->createDataSourceMockForCriteria(
            $criteria,
            $articles,
            count($articles)
        );

        $dataSourceService = new DataSourceService(
            [
                $articlesDataSource,
                $productsDataSource,
            ],
            $this->itemGroupStrategy
        );

        $items = $dataSourceService->getItems($criteria);

        self::assertCount(
            $products->count() + $articles->count(),
            $items
        );

        self::assertEquals(
            $this->itemCreator->createTestItemListForEnglishArticlesAndProducts(),
            $items
        );
    }

    public function testGetGroupedItems(): void
    {
        $criteria = $this->itemCreator->createTestCriteria(
            [
                ItemType::ARTICLE_IDENTIFIER,
                ItemType::BLOG_IDENTIFIER,
            ],
            ['en', 'de', 'fr']
        );

        $expectedGroupList = $this->itemCreator->createTestItemGroupListForArticlesAndBlogPosts();
        $dataSource = $this->createMock(DataSourceInterface::class);

        $itemGroupStrategy = $this->createMock(ItemGroupListStrategyInterface::class);
        $itemGroupStrategy
            ->expects(self::once())
            ->method('getGroupList')
            ->with($dataSource, $criteria, SupportedGroupItemStrategy::GROUP_BY_ITEM_TYPE_AND_LANGUAGE)
            ->willReturn($expectedGroupList);

        $dataSourceService = new DataSourceService(
            [
                $dataSource,
            ],
            $itemGroupStrategy
        );

        self::assertEquals(
            $expectedGroupList,
            $dataSourceService->getGroupedItems(
                $criteria,
                SupportedGroupItemStrategy::GROUP_BY_ITEM_TYPE_AND_LANGUAGE
            )
        );
    }

    /**
     * @return iterable<array{
     *  CriteriaInterface, ItemListInterface
     * }>
     */
    public function providerForTestGetItems(): iterable
    {
        yield [
            $this->itemCreator->createTestCriteria(
                [],
                ['pl']
            ),
            $this->itemCreator->createTestItemList(),
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
                ['en']
            ),
            $this->itemCreator->createTestItemListForEnglishArticlesAndProducts(),
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::BLOG_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
                ['en', 'fr', 'de', 'no']
            ),
            $this->createItems(),
        ];
    }

    private function createDataSourceMockForCriteria(
        CriteriaInterface $criteria,
        ItemListInterface $expectedItemList,
        int $expectedCount
    ): DataSourceInterface {
        $source = $this->createMock(DataSourceInterface::class);
        $source
            ->expects(self::once())
            ->method('countItems')
            ->with($criteria)
            ->willReturn($expectedCount);

        if ($expectedCount > 0) {
            $source
                ->expects(self::once())
                ->method('fetchItems')
                ->with($criteria)
                ->willReturn($expectedItemList);
        }

        return $source;
    }

    private function createDataSourceMockForGetItem(
        string $id,
        string $language,
        ItemInterface $expectedItem
    ): DataSourceInterface {
        $source = $this->createMock(DataSourceInterface::class);
        $source
            ->expects(self::once())
            ->method('fetchItem')
            ->with($id, $language)
            ->willReturn($expectedItem);

        return $source;
    }
}
