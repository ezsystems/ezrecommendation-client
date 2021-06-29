<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Creator;

use ArrayIterator;
use EzSystems\EzRecommendationClient\Tests\Stubs\Criteria;
use EzSystems\EzRecommendationClient\Tests\Stubs\Item;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemList;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;
use Ibexa\Contracts\Personalization\Value\ItemTypeInterface;
use Traversable;

final class DataSourceTestItemCreator
{
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
    public static function createTestItems(?iterable $itemsConfig = null): Traversable
    {
        return self::createTestItemsForConfig(
            $itemsConfig ?? self::getDefaultItemsConfig()
        );
    }

    public static function createTestItem(
        int $counter,
        string $itemId,
        string $itemTypeIdentifier,
        string $itemTypeName,
        string $language
    ): ItemInterface {
        return new Item(
            $itemId,
            self::createTestItemType($itemTypeIdentifier, $itemTypeName),
            $language,
            self::createTestItemAttributes(
                $counter,
                $itemTypeIdentifier,
                $itemTypeName,
                $language
            )
        );
    }

    public static function createTestItemType(string $identifier, string $name): ItemTypeInterface
    {
        return new ItemType(
            $identifier,
            $name
        );
    }

    /**
     * @return array<string, string|int|array>
     */
    public static function createTestItemAttributes(
        int $counter,
        string $itemTypeIdentifier,
        string $itemTypeName,
        string $language
    ): array {
        return [
            'name' => sprintf('%s %s %s', $itemTypeName, $counter, $language),
            'body' => sprintf('%s %s %s %s', $itemTypeName, $counter, Item::ITEM_BODY, $language),
            'image' => sprintf(Item::ITEM_IMAGE, $itemTypeIdentifier, $counter),
        ];
    }

    /**
     * @param array<string> $identifiers
     * @param array<string> $languages
     */
    public static function createTestCriteria(
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

    public static function createTestItemList(ItemInterface ...$items): ItemListInterface
    {
        return new ItemList($items);
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
    private static function createTestItemsForConfig(iterable $testItemsConfig): Traversable
    {
        $items = [];

        foreach (self::createTestItemsForConcreteConfig($testItemsConfig) as $itemGroup) {
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
    private static function createTestItemsForConcreteConfig(iterable $testItemsConfig): iterable
    {
        $items = [];
        $counter = 1;

        foreach ($testItemsConfig as $testItem) {
            $createdItems = self::createTestItemsForGivenLanguages(
                $counter,
                $testItem['item_type_identifier'],
                $testItem['item_type_name'],
                $testItem['languages'],
                $testItem['limit'],
            );
            $counter += count($createdItems);
            $items[] = $createdItems;
        }

        return $items;
    }

    /**
     * @param array<string> $languages
     *
     * @return array<\Ibexa\Contracts\Personalization\Value\ItemInterface>
     */
    private static function createTestItemsForGivenLanguages(
        int $id,
        string $itemTypeIdentifier,
        string $itemTypeName,
        array $languages,
        int $limit
    ): array {
        $items = [];

        foreach ($languages as $language) {
            for ($i = 1; $i <= $limit; ++$i) {
                $items[] = self::createTestItem(
                    $i,
                    (string)$id,
                    $itemTypeIdentifier,
                    $itemTypeName,
                    $language
                );

                ++$id;
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
    private static function getDefaultItemsConfig(): iterable
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
