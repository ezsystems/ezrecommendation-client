<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\PersonalizationClient\Service\Storage;

use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyDispatcherInterface;
use Ibexa\Contracts\PersonalizationClient\Criteria\CriteriaInterface;
use Ibexa\Contracts\PersonalizationClient\Storage\DataSourceInterface;
use Ibexa\Contracts\PersonalizationClient\Value\ItemInterface;
use Ibexa\Contracts\PersonalizationClient\Value\ItemListInterface;
use Ibexa\PersonalizationClient\Service\Storage\DataSourceService;
use Ibexa\PersonalizationClient\Strategy\Storage\SupportedGroupItemStrategy;
use Ibexa\Tests\PersonalizationClient\Creator\DataSourceTestItemCreator;
use Ibexa\Tests\PersonalizationClient\Storage\AbstractDataSourceTestCase;

final class DataSourceServiceTest extends AbstractDataSourceTestCase
{
    /** @var \EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private GroupItemStrategyDispatcherInterface $itemGroupStrategy;

    public function setUp(): void
    {
        $this->itemGroupStrategy = $this->createMock(GroupItemStrategyDispatcherInterface::class);
    }

    public function testGetItem(): void
    {
        $item = $this->itemCreator->createTestItem(
            1,
            '1',
            DataSourceTestItemCreator::ARTICLE_TYPE_ID,
            DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
            DataSourceTestItemCreator::ARTICLE_NAME,
            DataSourceTestItemCreator::LANGUAGE_EN
        );

        $dataSourceService = new DataSourceService(
            [
                $this->createDataSourceMockForGetItem(
                    '1',
                    DataSourceTestItemCreator::LANGUAGE_EN,
                    $item
                ),
            ],
            $this->itemGroupStrategy
        );

        self::assertEquals(
            $item,
            $dataSourceService->getItem('1', DataSourceTestItemCreator::LANGUAGE_EN)
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
                DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
                DataSourceTestItemCreator::PRODUCT_TYPE_IDENTIFIER,
            ],
            [DataSourceTestItemCreator::LANGUAGE_EN]
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
                DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
                DataSourceTestItemCreator::BLOG_TYPE_IDENTIFIER,
            ],
            [
                DataSourceTestItemCreator::LANGUAGE_EN,
                DataSourceTestItemCreator::LANGUAGE_DE,
                DataSourceTestItemCreator::LANGUAGE_FR,
            ]
        );

        $expectedGroupList = $this->itemCreator->createTestItemGroupListForArticlesAndBlogPosts();
        $dataSource = $this->createMock(DataSourceInterface::class);

        $this->itemGroupStrategy
            ->expects(self::once())
            ->method('getGroupList')
            ->with($dataSource, $criteria, SupportedGroupItemStrategy::GROUP_BY_ITEM_TYPE_AND_LANGUAGE)
            ->willReturn($expectedGroupList);

        $dataSourceService = new DataSourceService(
            [
                $dataSource,
            ],
            $this->itemGroupStrategy
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
                    DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
                    DataSourceTestItemCreator::PRODUCT_TYPE_IDENTIFIER,
                ],
                [DataSourceTestItemCreator::LANGUAGE_EN]
            ),
            $this->itemCreator->createTestItemListForEnglishArticlesAndProducts(),
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                [
                    DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
                    DataSourceTestItemCreator::BLOG_TYPE_IDENTIFIER,
                    DataSourceTestItemCreator::PRODUCT_TYPE_IDENTIFIER,
                ],
                DataSourceTestItemCreator::ALL_LANGUAGES
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
