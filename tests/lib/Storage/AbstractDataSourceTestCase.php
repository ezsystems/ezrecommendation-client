<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Storage;

use EzSystems\EzRecommendationClient\Exception\ItemNotFoundException;
use EzSystems\EzRecommendationClient\Tests\Creator\DataSourceTestItemCreator;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemList;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Storage\DataSourceInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractDataSourceTestCase extends TestCase
{
    abstract protected function createDataSource(ItemListInterface $itemList): DataSourceInterface;

    /**
     * @phpstan-param ?iterable<string, array{
     *  'item_type_identifier': string,
     *  'item_type_name': string,
     *  'languages': array<string>,
     *  'limit': int,
     * }> $itemsConfig
     */
    public function createItems(?iterable $itemsConfig = null): ItemListInterface
    {
        return ItemList::fromTraversable(
            DataSourceTestItemCreator::createTestItems($itemsConfig)
        );
    }

    /**
     * @dataProvider providerForTestCountItems
     */
    public function testCountItems(CriteriaInterface $criteria, int $expectedCount): void
    {
        $dataSource = $this->createDataSource($this->createItems());
        $this->assertCountItems($dataSource, $criteria, $expectedCount);
    }

    /**
     * @param iterable<\Ibexa\Contracts\Personalization\Value\ItemInterface> $expectedItems
     *
     * @dataProvider providerForTestFetchItems
     * @dataProvider providerForTestFetchItemListWithLimit
     * @dataProvider providerForTestFetchItemListWithLimitAndOffset
     */
    public function testFetchItems(CriteriaInterface $criteria, iterable $expectedItems): void
    {
        $dataSource = $this->createDataSource($this->createItems());
        $this->assertFetchItems($dataSource, $criteria, $expectedItems);
    }

    public function testFetchItem(): void
    {
        $dataSource = $this->createDataSource($this->createItems());

        $counter = 1;
        $articleId = '1';
        $articleName = 'Article';
        $articleLanguage = 'en';

        $this->assertFetchItem(
            $dataSource,
            $articleId,
            $articleLanguage,
            DataSourceTestItemCreator::createTestItem(
                $counter,
                $articleId,
                ItemType::ARTICLE_IDENTIFIER,
                $articleName,
                $articleLanguage
            )
        );
    }

    public function testFetchNonexistentItem(): void
    {
        $this->exceptExceptionsOnFetchNonexistentItem();
    }

    /**
     * @return iterable<array{CriteriaInterface, int}>
     */
    public function providerForTestCountItems(): iterable
    {
        yield [
            DataSourceTestItemCreator::createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
                ['pl']
            ),
            0,
        ];

        yield [
            DataSourceTestItemCreator::createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                ],
                ['en']
            ),
            2,
        ];

        yield [
            DataSourceTestItemCreator::createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::BLOG_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
                ['en']
            ),
            15,
        ];

        yield [
            DataSourceTestItemCreator::createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::BLOG_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
                ['en', 'no']
            ),
            25,
        ];

        yield [
            DataSourceTestItemCreator::createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::BLOG_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
                ['en', 'fr', 'de']
            ),
            40,
        ];

        yield [
            DataSourceTestItemCreator::createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::BLOG_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
                ['en', 'fr', 'de', 'no']
            ),
            50,
        ];
    }

    /**
     * @return iterable<array{CriteriaInterface, ItemListInterface}>
     */
    public function providerForTestFetchItems(): iterable
    {
        yield [
            DataSourceTestItemCreator::createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
                ['pl']
            ),
            DataSourceTestItemCreator::createTestEmptyItemList(),
        ];

        yield [
            DataSourceTestItemCreator::createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
                ['en']
            ),
            DataSourceTestItemCreator::createTestItemList(
                DataSourceTestItemCreator::createTestItem(
                    1,
                    '1',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    2,
                    '2',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    1,
                    '11',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    2,
                    '12',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    3,
                    '13',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    4,
                    '14',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    5,
                    '15',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    6,
                    '16',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    7,
                    '17',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    8,
                    '18',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    9,
                    '19',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    10,
                    '20',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
            ),
        ];
    }

    /**
     * @return iterable<array{CriteriaInterface, ItemListInterface}>
     */
    public function providerForTestFetchItemListWithLimit(): iterable
    {
        yield [
            DataSourceTestItemCreator::createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::BLOG_IDENTIFIER,
                ],
                ['en', 'fr', 'de'],
                7
            ),
            DataSourceTestItemCreator::createTestItemList(
                DataSourceTestItemCreator::createTestItem(
                    1,
                    '1',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    2,
                    '2',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    1,
                    '3',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'de'
                ),
                DataSourceTestItemCreator::createTestItem(
                    2,
                    '4',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'de'
                ),
                DataSourceTestItemCreator::createTestItem(
                    1,
                    '5',
                    ItemType::BLOG_IDENTIFIER,
                    'Blog',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    2,
                    '6',
                    ItemType::BLOG_IDENTIFIER,
                    'Blog',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    3,
                    '7',
                    ItemType::BLOG_IDENTIFIER,
                    'Blog',
                    'en'
                ),
            ),
        ];
    }

    /**
     * @return iterable<array{CriteriaInterface, ItemListInterface}>
     */
    public function providerForTestFetchItemListWithLimitAndOffset(): iterable
    {
        yield [
            DataSourceTestItemCreator::createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::BLOG_IDENTIFIER,
                ],
                ['en', 'fr', 'de'],
                5,
                2
            ),
            DataSourceTestItemCreator::createTestItemList(
                DataSourceTestItemCreator::createTestItem(
                    1,
                    '3',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'de'
                ),
                DataSourceTestItemCreator::createTestItem(
                    2,
                    '4',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'de'
                ),
                DataSourceTestItemCreator::createTestItem(
                    1,
                    '5',
                    ItemType::BLOG_IDENTIFIER,
                    'Blog',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    2,
                    '6',
                    ItemType::BLOG_IDENTIFIER,
                    'Blog',
                    'en'
                ),
                DataSourceTestItemCreator::createTestItem(
                    3,
                    '7',
                    ItemType::BLOG_IDENTIFIER,
                    'Blog',
                    'en'
                ),
            ),
        ];
    }

    /**
     * @param iterable<\Ibexa\Contracts\Personalization\Value\ItemInterface> $expectedItems
     */
    protected function assertFetchItems(
        DataSourceInterface $source,
        CriteriaInterface $criteria,
        iterable $expectedItems
    ): void {
        self::assertEquals(
            $expectedItems,
            $source->fetchItems($criteria)
        );
    }

    protected function assertCountItems(
        DataSourceInterface $source,
        CriteriaInterface $criteria,
        int $expectedCount
    ): void {
        self::assertCount(
            $expectedCount,
            $source->fetchItems($criteria)
        );

        self::assertEquals(
            $expectedCount,
            $source->countItems($criteria)
        );
    }

    protected function assertFetchItem(
        DataSourceInterface $source,
        string $id,
        string $language,
        ItemInterface $expectedItem
    ): void {
        self::assertEquals(
            $expectedItem,
            $source->fetchItem($id, $language)
        );
    }

    protected function exceptExceptionsOnFetchNonexistentItem(): void
    {
        $dataSource = $this->createDataSource($this->createItems());

        $this->expectException(ItemNotFoundException::class);
        $this->expectExceptionMessage('Item not found with id: undefined_item and language: pl');

        $dataSource->fetchItem('undefined_item', 'pl');
    }
}
