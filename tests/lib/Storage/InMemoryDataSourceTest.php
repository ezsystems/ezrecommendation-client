<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Storage;

use EzSystems\EzRecommendationClient\Storage\InMemoryDataSource;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemList;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Storage\DataSourceInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;

final class InMemoryDataSourceTest extends AbstractDataSourceTestCase
{
    private DataSourceInterface $inMemoryDataSource;

    public function setUp(): void
    {
        parent::setUp();

        $config = $this->getItemsConfig();
        $testDataSourceItems = iterator_to_array($this->getDataSourceTestItems($config));
        $this->inMemoryDataSource = new InMemoryDataSource(new ItemList($testDataSourceItems));
    }

    /**
     * @dataProvider providerForTestCountItems
     */
    public function testCountItems(CriteriaInterface $criteria, int $expectedCount): void
    {
        $this->assertCountItems($this->inMemoryDataSource, $criteria, $expectedCount);
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
        $this->assertFetchItems($this->inMemoryDataSource, $criteria, $expectedItems);
    }

    public function testFetchItem(): void
    {
        $counter = 1;
        $articleId = '1';
        $articleName = 'Article';
        $articleLanguage = 'en';

        $this->assertFetchItem(
            $this->inMemoryDataSource,
            $articleId,
            $articleLanguage,
            $this->createTestItem(
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
        $this->exceptExceptionsOnFetchNonexistentItem($this->inMemoryDataSource);
    }

    /**
     * @return iterable<array{CriteriaInterface, int}>
     */
    public function providerForTestCountItems(): iterable
    {
        yield [
            $this->createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
                ['pl']
            ),
            0,
        ];

        yield [
            $this->createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                ],
                ['en']
            ),
            2,
        ];

        yield [
            $this->createTestCriteria(
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
            $this->createTestCriteria(
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
            $this->createTestCriteria(
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
            $this->createTestCriteria(
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
            $this->createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
                ['pl']
            ),
            $this->createTestEmptyItemList(),
        ];

        yield [
            $this->createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::PRODUCT_IDENTIFIER,
                ],
                ['en']
            ),
            $this->createTestItemList(
                $this->createTestItem(
                    1,
                    '1',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'en'
                ),
                $this->createTestItem(
                    2,
                    '2',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'en'
                ),
                $this->createTestItem(
                    1,
                    '11',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->createTestItem(
                    2,
                    '12',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->createTestItem(
                    3,
                    '13',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->createTestItem(
                    4,
                    '14',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->createTestItem(
                    5,
                    '15',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->createTestItem(
                    6,
                    '16',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->createTestItem(
                    7,
                    '17',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->createTestItem(
                    8,
                    '18',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->createTestItem(
                    9,
                    '19',
                    ItemType::PRODUCT_IDENTIFIER,
                    'Product',
                    'en'
                ),
                $this->createTestItem(
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
            $this->createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::BLOG_IDENTIFIER,
                ],
                ['en', 'fr', 'de'],
                7
            ),
            $this->createTestItemList(
                $this->createTestItem(
                    1,
                    '1',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'en'
                ),
                $this->createTestItem(
                    2,
                    '2',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'en'
                ),
                $this->createTestItem(
                    1,
                    '3',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'de'
                ),
                $this->createTestItem(
                    2,
                    '4',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'de'
                ),
                $this->createTestItem(
                    1,
                    '5',
                    ItemType::BLOG_IDENTIFIER,
                    'Blog',
                    'en'
                ),
                $this->createTestItem(
                    2,
                    '6',
                    ItemType::BLOG_IDENTIFIER,
                    'Blog',
                    'en'
                ),
                $this->createTestItem(
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
            $this->createTestCriteria(
                [
                    ItemType::ARTICLE_IDENTIFIER,
                    ItemType::BLOG_IDENTIFIER,
                ],
                ['en', 'fr', 'de'],
                5,
                2
            ),
            $this->createTestItemList(
                $this->createTestItem(
                    1,
                    '3',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'de'
                ),
                $this->createTestItem(
                    2,
                    '4',
                    ItemType::ARTICLE_IDENTIFIER,
                    'Article',
                    'de'
                ),
                $this->createTestItem(
                    1,
                    '5',
                    ItemType::BLOG_IDENTIFIER,
                    'Blog',
                    'en'
                ),
                $this->createTestItem(
                    2,
                    '6',
                    ItemType::BLOG_IDENTIFIER,
                    'Blog',
                    'en'
                ),
                $this->createTestItem(
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
     * @phpstan-return iterable<string, array{
     *  'item_type_identifier': string,
     *  'item_type_name': string,
     *  'languages': array<string>,
     *  'limit': int,
     * }>
     */
    private function getItemsConfig(): iterable
    {
        yield 'articles' => [
            'item_type_identifier' => ItemType::ARTICLE_IDENTIFIER,
            'item_type_name' => 'Article',
            'languages' => ['en', 'de'],
            'limit' => 2,
        ];

        yield 'blog posts' => [
            'item_type_identifier' => ItemType::BLOG_IDENTIFIER,
            'item_type_name' => 'Blog',
            'languages' => ['en', 'fr'],
            'limit' => 3,
        ];

        yield 'products' => [
            'item_type_identifier' => ItemType::PRODUCT_IDENTIFIER,
            'item_type_name' => 'Product',
            'languages' => ['en', 'de', 'fr', 'no'],
            'limit' => 10,
        ];
    }
}
