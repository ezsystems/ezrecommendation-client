<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Creator;

use ArrayIterator;
use EzSystems\EzRecommendationClient\Criteria\Criteria;
use EzSystems\EzRecommendationClient\Value\Storage\Item;
use EzSystems\EzRecommendationClient\Value\Storage\ItemGroup;
use EzSystems\EzRecommendationClient\Value\Storage\ItemGroupList;
use EzSystems\EzRecommendationClient\Value\Storage\ItemList;
use EzSystems\EzRecommendationClient\Value\Storage\ItemType;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Value\ItemGroupInterface;
use Ibexa\Contracts\Personalization\Value\ItemGroupListInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;
use Ibexa\Contracts\Personalization\Value\ItemTypeInterface;
use Traversable;

final class DataSourceTestItemCreator
{
    public const ITEM_BODY = 'body';
    public const ITEM_IMAGE = 'public/var/1/2/4/5/%s/%s';
    public const ARTICLE_IDENTIFIER = 'article';
    public const BLOG_IDENTIFIER = 'blog';
    public const PRODUCT_IDENTIFIER = 'product';
    public const ARTICLE_NAME = 'Article';
    public const BLOG_NAME = 'Blog';
    public const PRODUCT_NAME = 'Product';

    private int $lastGeneratedId = 1;

    /**
     * @phpstan-param ?iterable<string, array{
     *  'item_type_identifier': string,
     *  'item_type_name': string,
     *  'languages': array<string>,
     *  'limit': int,
     * }> $itemsConfig
     *
     * @return Traversable<\Ibexa\Contracts\Personalization\Value\ItemInterface>
     */
    public function createTestItems(?iterable $itemsConfig = null): Traversable
    {
        return $this->createTestItemsForConfig(
            $itemsConfig ?? $this->getDefaultItemsConfig()
        );
    }

    public function createTestItem(
        int $counter,
        string $itemId,
        string $itemTypeIdentifier,
        string $itemTypeName,
        string $language
    ): ItemInterface {
        return new Item(
            $itemId,
            $this->createTestItemType($itemTypeIdentifier, $itemTypeName),
            $language,
            $this->createTestItemAttributes(
                $counter,
                $itemTypeIdentifier,
                $itemTypeName,
                $language
            )
        );
    }

    public function createTestItemType(string $identifier, string $name): ItemTypeInterface
    {
        return new ItemType(
            $identifier,
            $name
        );
    }

    /**
     * @return array<string, string>
     */
    public function createTestItemAttributes(
        int $counter,
        string $itemTypeIdentifier,
        string $itemTypeName,
        string $language
    ): array {
        return [
            'name' => sprintf('%s %s %s', $itemTypeName, $counter, $language),
            'body' => sprintf('%s %s %s %s', $itemTypeName, $counter, self::ITEM_BODY, $language),
            'image' => sprintf(self::ITEM_IMAGE, $itemTypeIdentifier, $counter),
        ];
    }

    /**
     * @param array<string> $identifiers
     * @param array<string> $languages
     */
    public function createTestCriteria(
        array $identifiers,
        array $languages,
        int $limit = Criteria::LIMIT,
        int $offset = 0
    ): CriteriaInterface {
        return new Criteria(
            $identifiers,
            $languages,
            $limit,
            $offset
        );
    }

    public function createTestItemList(ItemInterface ...$items): ItemListInterface
    {
        return new ItemList($items);
    }

    public function createTestItemGroup(string $identifier, ItemListInterface $itemList): ItemGroupInterface
    {
        return new ItemGroup($identifier, $itemList);
    }

    public function createTestItemGroupList(ItemGroupInterface ...$groups): ItemGroupListInterface
    {
        return new ItemGroupList($groups);
    }

    public function createTestItemGroupListForArticlesAndBlogPosts(): ItemGroupListInterface
    {
        return $this->createTestItemGroupList(
            $this->createTestItemGroup(
                self::ARTICLE_IDENTIFIER . '_' . 'en',
                $this->createTestItemListForEnglishArticles()
            ),
            $this->createTestItemGroup(
                self::ARTICLE_IDENTIFIER . '_' . 'de',
                $this->createTestItemListForGermanArticles()
            ),
            $this->createTestItemGroup(
                self::BLOG_IDENTIFIER . '_' . 'en',
                $this->createTestItemListForEnglishBlogPosts()
            ),
            $this->createTestItemGroup(
                self::BLOG_IDENTIFIER . '_' . 'fr',
                $this->createTestItemListForFrenchBlogPosts()
            ),
        );
    }

    public function createTestItemListForEnglishArticles(): ItemListInterface
    {
        return new ItemList($this->createTestItemsForEnglishArticles());
    }

    public function createTestItemListForGermanArticles(): ItemListInterface
    {
        return $this->createTestItemList(
            $this->createTestItem(
                1,
                '3',
                self::ARTICLE_IDENTIFIER,
                self::ARTICLE_NAME,
                'de'
            ),
            $this->createTestItem(
                2,
                '4',
                self::ARTICLE_IDENTIFIER,
                self::ARTICLE_NAME,
                'de'
            ),
        );
    }

    public function createTestItemListForEnglishBlogPosts(): ItemListInterface
    {
        return new ItemList($this->createTestItemsForEnglishBlogPosts());
    }

    public function createTestItemListForFrenchBlogPosts(): ItemListInterface
    {
        return $this->createTestItemList(
            $this->createTestItem(
                1,
                '8',
                self::BLOG_IDENTIFIER,
                self::BLOG_IDENTIFIER,
                'fr'
            ),
            $this->createTestItem(
                2,
                '9',
                self::BLOG_IDENTIFIER,
                self::BLOG_NAME,
                'fr'
            ),
            $this->createTestItem(
                3,
                '10',
                self::BLOG_IDENTIFIER,
                self::BLOG_NAME,
                'fr'
            ),
        );
    }

    public function createTestItemListForEnglishProducts(): ItemListInterface
    {
        return new ItemList($this->createTestItemsForEnglishProducts());
    }

    /**
     * @return array<\Ibexa\Contracts\Personalization\Value\ItemInterface>
     */
    public function createTestItemsForEnglishArticles(): array
    {
        return [
            $this->createTestItem(
                1,
                '1',
                self::ARTICLE_IDENTIFIER,
                self::ARTICLE_NAME,
                'en'
            ),
            $this->createTestItem(
                2,
                '2',
                self::ARTICLE_IDENTIFIER,
                self::ARTICLE_NAME,
                'en'
            ),
        ];
    }

    /**
     * @return array<\Ibexa\Contracts\Personalization\Value\ItemInterface>
     */
    public function createTestItemsForGermanArticles(): array
    {
        return [
            $this->createTestItem(
                1,
                '3',
                DataSourceTestItemCreator::ARTICLE_IDENTIFIER,
                DataSourceTestItemCreator::ARTICLE_NAME,
                'de'
            ),
            $this->createTestItem(
                2,
                '4',
                DataSourceTestItemCreator::ARTICLE_IDENTIFIER,
                DataSourceTestItemCreator::ARTICLE_NAME,
                'de'
            ),
        ];
    }

    /**
     * @return array<\Ibexa\Contracts\Personalization\Value\ItemInterface>
     */
    public function createTestItemsForEnglishBlogPosts(): array
    {
        return [
            $this->createTestItem(
                1,
                '5',
                self::BLOG_IDENTIFIER,
                self::BLOG_NAME,
                'en'
            ),
            $this->createTestItem(
                2,
                '6',
                self::BLOG_IDENTIFIER,
                self::BLOG_NAME,
                'en'
            ),
            $this->createTestItem(
                3,
                '7',
                self::BLOG_IDENTIFIER,
                self::BLOG_NAME,
                'en'
            ),
        ];
    }

    /**
     * @return array<\Ibexa\Contracts\Personalization\Value\ItemInterface>
     */
    public function createTestItemsForEnglishProducts(): array
    {
        $products = [];
        $id = 10;

        for ($i = 1; $i <= 10; ++$i) {
            ++$id;
            $products[] = $this->createTestItem(
                $i,
                (string)$id,
                self::PRODUCT_IDENTIFIER,
                self::PRODUCT_NAME,
                'en'
            );
        }

        return $products;
    }

    public function createTestItemListForEnglishArticlesAndProducts(): ItemListInterface
    {
        return new ItemList(
            array_merge(
                $this->createTestItemsForEnglishArticles(),
                $this->createTestItemsForEnglishProducts(),
            )
        );
    }

    /**
     * @return array<string>
     */
    public function getAllTestItemTypeIdentifiers(): array
    {
        return [
            self::ARTICLE_IDENTIFIER,
            self::BLOG_IDENTIFIER,
            self::PRODUCT_IDENTIFIER,
        ];
    }

    /**
     * @phpstan-param array<string, array{
     *  'item_type_identifier': string,
     *  'item_type_name': string,
     *  'languages': array,
     *  'limit': int,
     * }> $testItemsConfig
     *
     * @return Traversable<\Ibexa\Contracts\Personalization\Value\ItemInterface>
     */
    private function createTestItemsForConfig(iterable $testItemsConfig): Traversable
    {
        $items = [];
        $this->lastGeneratedId = 1;

        foreach ($this->createTestItemsForConcreteConfig($testItemsConfig) as $itemGroup) {
            foreach ($itemGroup as $item) {
                $items[] = $item;
            }
        }

        return new ArrayIterator($items);
    }

    /**
     * @phpstan-param array<string, array{
     *  'item_type_identifier': string,
     *  'item_type_name': string,
     *  'languages': array,
     *  'limit': int,
     * }> $testItemsConfig
     *
     * @return iterable<int, iterable<\Ibexa\Contracts\Personalization\Value\ItemInterface>>
     */
    private function createTestItemsForConcreteConfig(iterable $testItemsConfig): iterable
    {
        $items = [];

        foreach ($testItemsConfig as $testItem) {
            $items[] = $this->createTestItemsForGivenLanguages(
                $testItem['item_type_identifier'],
                $testItem['item_type_name'],
                $testItem['languages'],
                $testItem['limit'],
            );
        }

        return $items;
    }

    /**
     * @param array<string> $languages
     *
     * @return array<\Ibexa\Contracts\Personalization\Value\ItemInterface>
     */
    private function createTestItemsForGivenLanguages(
        string $itemTypeIdentifier,
        string $itemTypeName,
        array $languages,
        int $limit
    ): array {
        $items = [];

        foreach ($languages as $language) {
            for ($i = 1; $i <= $limit; ++$i) {
                $items[] = $this->createTestItem(
                    $i,
                    (string)$this->lastGeneratedId,
                    $itemTypeIdentifier,
                    $itemTypeName,
                    $language
                );

                ++$this->lastGeneratedId;
            }
        }

        return $items;
    }

    /**
     * @phpstan-return iterable<string, array{
     *  'item_type_identifier': string,
     *  'item_type_name': string,
     *  'languages': array<string>,
     *  'limit': int,
     * }>
     */
    private function getDefaultItemsConfig(): iterable
    {
        yield 'articles' => [
            'item_type_identifier' => self::ARTICLE_IDENTIFIER,
            'item_type_name' => 'Article',
            'languages' => ['en', 'de'],
            'limit' => 2,
        ];

        yield 'blog posts' => [
            'item_type_identifier' => self::BLOG_IDENTIFIER,
            'item_type_name' => 'Blog',
            'languages' => ['en', 'fr'],
            'limit' => 3,
        ];

        yield 'products' => [
            'item_type_identifier' => self::PRODUCT_IDENTIFIER,
            'item_type_name' => 'Product',
            'languages' => ['en', 'de', 'fr', 'no'],
            'limit' => 10,
        ];
    }
}
