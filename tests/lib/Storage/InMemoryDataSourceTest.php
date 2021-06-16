<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Storage;

use EzSystems\EzRecommendationClient\Exception\ItemNotFoundException;
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
    public function testCountItems(CriteriaInterface $criteria, int $expected): void
    {
        self::assertCount(
            $expected,
            $this->inMemoryDataSource->fetchItems($criteria)
        );

        self::assertEquals(
            $expected,
            $this->inMemoryDataSource->countItems($criteria)
        );
    }

    /**
     * @param iterable<\Ibexa\Contracts\Personalization\Value\ItemInterface> $expectedItems
     *
     * @dataProvider providerForTestFetchItems
     */
    public function testFetchItems(CriteriaInterface $criteria, iterable $expectedItems): void
    {
        self::assertEquals(
            $expectedItems,
            $this->inMemoryDataSource->fetchItems($criteria)
        );
    }

    public function testFetchItem(): void
    {
        $counter = 1;
        $articleId = '1';
        $articleName = 'Article';
        $articleLanguage = 'en';

        self::assertEquals(
            $this->createTestItem(
                $counter,
                $articleId,
                ItemType::ARTICLE_IDENTIFIER,
                $articleName,
                $articleLanguage
            ),
            $this->inMemoryDataSource->fetchItem($articleId, $articleLanguage)
        );
    }

    public function testFetchNonexistentItem(): void
    {
        $this->expectException(ItemNotFoundException::class);
        $this->expectExceptionMessage('Item not found with id: 12345678 and language: pl');

        $this->inMemoryDataSource->fetchItem('12345678', 'pl');
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
            6,
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
            7,
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
            13,
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
            14,
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
            'limit' => 1,
        ];
    }
}
