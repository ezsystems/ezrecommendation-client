<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Personalization\Storage;

use EzSystems\EzRecommendationClient\Exception\ItemNotFoundException;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Storage\DataSourceInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;
use Ibexa\Personalization\Value\Storage\ItemList;
use Ibexa\Tests\Personalization\Creator\DataSourceTestItemCreator;

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
        $articleLanguage = 'en';

        $this->assertFetchItem(
            $dataSource,
            $articleId,
            $articleLanguage,
            $this->itemCreator->createTestItem(
                $counter,
                $articleId,
                DataSourceTestItemCreator::ARTICLE_IDENTIFIER,
                DataSourceTestItemCreator::ARTICLE_NAME,
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
                    DataSourceTestItemCreator::ARTICLE_IDENTIFIER,
                    DataSourceTestItemCreator::PRODUCT_IDENTIFIER,
                ],
                ['pl']
            ),
            0,
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                [
                    DataSourceTestItemCreator::ARTICLE_IDENTIFIER,
                ],
                ['en']
            ),
            2,
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                $this->itemCreator->getAllTestItemTypeIdentifiers(),
                ['en']
            ),
            15,
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                $this->itemCreator->getAllTestItemTypeIdentifiers(),
                ['en', 'no']
            ),
            25,
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                $this->itemCreator->getAllTestItemTypeIdentifiers(),
                ['en', 'fr', 'de']
            ),
            40,
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                $this->itemCreator->getAllTestItemTypeIdentifiers(),
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
                [DataSourceTestItemCreator::ARTICLE_IDENTIFIER],
                []
            ),
            $this->itemCreator->createTestItemList(),
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                [
                    DataSourceTestItemCreator::ARTICLE_IDENTIFIER,
                    DataSourceTestItemCreator::PRODUCT_IDENTIFIER,
                ],
                ['pl']
            ),
            $this->itemCreator->createTestItemList(),
        ];

        yield [
            $this->itemCreator->createTestCriteria(
                [
                    DataSourceTestItemCreator::ARTICLE_IDENTIFIER,
                    DataSourceTestItemCreator::PRODUCT_IDENTIFIER,
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
                    DataSourceTestItemCreator::ARTICLE_IDENTIFIER,
                    DataSourceTestItemCreator::BLOG_IDENTIFIER,
                ],
                ['en', 'fr', 'de'],
                7
            ),
            new ItemList(
                array_merge(
                    $this->itemCreator->createTestItemsForEnglishArticles(),
                    $this->itemCreator->createTestItemsForGermanArticles(),
                    $this->itemCreator->createTestItemsForEnglishBlogPosts(),
                )
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
                    DataSourceTestItemCreator::ARTICLE_IDENTIFIER,
                    DataSourceTestItemCreator::BLOG_IDENTIFIER,
                ],
                ['en', 'fr', 'de'],
                5,
                2
            ),
            new ItemList(
                array_merge(
                    $this->itemCreator->createTestItemsForGermanArticles(),
                    $this->itemCreator->createTestItemsForEnglishBlogPosts()
                )
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
