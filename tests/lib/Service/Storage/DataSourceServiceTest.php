<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Service\Storage;

use EzSystems\EzRecommendationClient\Service\Storage\DataSourceService;
use EzSystems\EzRecommendationClient\Storage\InMemoryDataSource;
use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyContextInterface;
use EzSystems\EzRecommendationClient\Tests\Storage\AbstractDataSourceTestCase;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Storage\DataSourceInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;

final class DataSourceServiceTest extends AbstractDataSourceTestCase
{
    private GroupItemStrategyContextInterface $itemGroupStrategy;

    public function setUp(): void
    {
        $this->itemGroupStrategy = $this->createMock(GroupItemStrategyContextInterface::class);
    }

    public function testGetItem(): void
    {
        $item = $this->itemCreator->createTestItem(
            1,
            '1',
            ItemType::ARTICLE_IDENTIFIER,
            'Article',
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

    public function testGetGroupedItems(): void
    {
        $criteria = $this->itemCreator->createTestCriteria(
            [
                ItemType::ARTICLE_IDENTIFIER,
                ItemType::BLOG_IDENTIFIER,
            ],
            ['en', 'de', 'fr']
        );

        $articleListEn = $this->itemCreator->createTestItemList(
            $this->itemCreator->createTestItem(
                1,
                '1',
                ItemType::ARTICLE_IDENTIFIER,
                'Article',
                'en'
            ),
            $this->itemCreator->createTestItem(
                2,
                '2',
                ItemType::ARTICLE_IDENTIFIER,
                'Article',
                'en'
            ),
        );

        $articleListDe = $this->itemCreator->createTestItemList(
            $this->itemCreator->createTestItem(
                1,
                '3',
                ItemType::ARTICLE_IDENTIFIER,
                'Article',
                'de'
            ),
            $this->itemCreator->createTestItem(
                2,
                '4',
                ItemType::ARTICLE_IDENTIFIER,
                'Article',
                'de'
            ),
        );

        $blogListEn = $this->itemCreator->createTestItemList(
            $this->itemCreator->createTestItem(
                1,
                '5',
                ItemType::BLOG_IDENTIFIER,
                'Blog post',
                'en'
            ),
            $this->itemCreator->createTestItem(
                2,
                '6',
                ItemType::BLOG_IDENTIFIER,
                'Blog post',
                'en'
            ),
            $this->itemCreator->createTestItem(
                3,
                '7',
                ItemType::BLOG_IDENTIFIER,
                'Blog post',
                'en'
            ),
        );

        $blogListFr = $this->itemCreator->createTestItemList(
            $this->itemCreator->createTestItem(
                1,
                '8',
                ItemType::BLOG_IDENTIFIER,
                'Blog post',
                'fr'
            ),
            $this->itemCreator->createTestItem(
                2,
                '9',
                ItemType::BLOG_IDENTIFIER,
                'Blog post',
                'fr'
            ),
            $this->itemCreator->createTestItem(
                3,
                '10',
                ItemType::BLOG_IDENTIFIER,
                'Blog post',
                'fr'
            ),
        );

        $expectedGroupList = $this->itemCreator->createTestItemGroupList(
            $this->itemCreator->createTestItemGroup(
                ItemType::ARTICLE_IDENTIFIER . '_' . 'en',
                $articleListEn
            ),
            $this->itemCreator->createTestItemGroup(
                ItemType::ARTICLE_IDENTIFIER . '_' . 'de',
                $articleListDe
            ),
            $this->itemCreator->createTestItemGroup(
                ItemType::BLOG_IDENTIFIER . '_' . 'en',
                $blogListEn
            ),
            $this->itemCreator->createTestItemGroup(
                ItemType::BLOG_IDENTIFIER . '_' . 'fr',
                $blogListFr
            ),
        );

        $itemGroupStrategy = $this->createMock(GroupItemStrategyContextInterface::class);
        $itemGroupStrategy
            ->method('getGroupList')
            ->with($criteria, 'item_type_and_language')
            ->willReturn($expectedGroupList);

        $dataSourceService = new DataSourceService(
            [
                new InMemoryDataSource($this->createItems()),
            ],
            $itemGroupStrategy
        );

        self::assertEquals(
            $expectedGroupList,
            $dataSourceService->getGroupedItems(
                $criteria,
                'item_type_and_language'
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
            $this->itemCreator->createTestItemList(
                $this->itemCreator->createTestItem(
                    1,
                    '1',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    2,
                    '2',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    1,
                    '11',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    2,
                    '12',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    3,
                    '13',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    4,
                    '14',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    5,
                    '15',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    6,
                    '16',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    7,
                    '17',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    8,
                    '18',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    9,
                    '19',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    10,
                    '20',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
            ),
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
        /** @var DataSourceInterface|\PHPUnit\Framework\MockObject\MockObject $source */
        $source = $this->createDataSourceMock();
        $source
            ->expects(self::once())
            ->method('countItems')
            ->with($criteria)
            ->willReturn(
                $expectedCount
            );

        if ($expectedCount > 0) {
            $source
                ->expects(self::once())
                ->method('fetchItems')
                ->with($criteria)
                ->willReturn(
                    $expectedItemList
                );
        }

        return $source;
    }

    private function createDataSourceMockForGetItem(
        string $id,
        string $language,
        ItemInterface $expectedItem
    ): DataSourceInterface {
        /** @var DataSourceInterface|\PHPUnit\Framework\MockObject\MockObject $source */
        $source = $this->createDataSourceMock();
        $source
            ->expects(self::once())
            ->method('fetchItem')
            ->with($id, $language)
            ->willReturn($expectedItem);

        return $source;
    }

    private function createDataSourceMock(): DataSourceInterface
    {
        return $this->createMock(DataSourceInterface::class);
    }
}
