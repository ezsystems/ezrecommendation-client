<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Storage;

use EzSystems\EzRecommendationClient\Exception\ItemNotFoundException;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Storage\DataSourceInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;

abstract class AbstractItemTestCase extends AbstractDataSourceTestCase
{
    abstract protected function createDataSource(ItemListInterface $itemList): DataSourceInterface;

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
        $articleName = ItemType::ARTICLE_NAME;
        $articleLanguage = 'en';

        $this->assertFetchItem(
            $dataSource,
            $articleId,
            $articleLanguage,
            $this->itemCreator->createTestItem(
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
        $this->expectExceptionOnFetchNonexistentItem();
    }

    /**
     * @return iterable<array{CriteriaInterface, int}>
     */
    public function providerForTestCountItems(): iterable
    {
        yield [
            $this->itemCreator->createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
                ['pl']
            ),
            0,
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                ],
                ['en']
            ),
            2,
        ];

        yield [
            $this->itemCreator->createTestCriteria(
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
            $this->itemCreator->createTestCriteria(
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
            $this->itemCreator->createTestCriteria(
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
            $this->itemCreator->createTestCriteria(
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
            $this->itemCreator->createTestCriteria(
                [],
                []
            ),
            $this->itemCreator->createTestItemList(),
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                [],
                ['pl']
            ),
            $this->itemCreator->createTestItemList(),
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                [ItemType::ARTICLE_IDENTIFIER],
                []
            ),
            $this->itemCreator->createTestItemList(),
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
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
    }

    /**
     * @return iterable<array{CriteriaInterface, ItemListInterface}>
     */
    public function providerForTestFetchItemListWithLimit(): iterable
    {
        yield [
            $this->itemCreator->createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::BLOG_IDENTIFIER,
                ],
                ['en', 'fr', 'de'],
                7
            ),
            $this->itemCreator->createTestItemList(
                $this->itemCreator->createTestItem(
                    1,
                    '1',
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::ARTICLE_NAME,
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    2,
                    '2',
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::ARTICLE_NAME,
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    1,
                    '3',
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::ARTICLE_NAME,
                    'de'
                ),
                $this->itemCreator->createTestItem(
                    2,
                    '4',
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::ARTICLE_NAME,
                    'de'
                ),
                $this->itemCreator->createTestItem(
                    1,
                    '5',
                    ItemType::BLOG_IDENTIFIER,
                    ItemType::BLOG_NAME,
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    2,
                    '6',
                    ItemType::BLOG_IDENTIFIER,
                    ItemType::BLOG_NAME,
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    3,
                    '7',
                    ItemType::BLOG_IDENTIFIER,
                    ItemType::BLOG_NAME,
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
            $this->itemCreator->createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::BLOG_IDENTIFIER,
                ],
                ['en', 'fr', 'de'],
                5,
                2
            ),
            $this->itemCreator->createTestItemList(
                $this->itemCreator->createTestItem(
                    1,
                    '3',
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::ARTICLE_NAME,
                    'de'
                ),
                $this->itemCreator->createTestItem(
                    2,
                    '4',
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::ARTICLE_NAME,
                    'de'
                ),
                $this->itemCreator->createTestItem(
                    1,
                    '5',
                    ItemType::BLOG_IDENTIFIER,
                    ItemType::BLOG_NAME,
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    2,
                    '6',
                    ItemType::BLOG_IDENTIFIER,
                    ItemType::BLOG_NAME,
                    'en'
                ),
                $this->itemCreator->createTestItem(
                    3,
                    '7',
                    ItemType::BLOG_IDENTIFIER,
                    ItemType::BLOG_NAME,
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

    protected function expectExceptionOnFetchNonexistentItem(): void
    {
        $dataSource = $this->createDataSource($this->createItems());

        $this->expectException(ItemNotFoundException::class);
        $this->expectExceptionMessage('Item not found with id: undefined_item and language: pl');

        $dataSource->fetchItem('undefined_item', 'pl');
    }
}
