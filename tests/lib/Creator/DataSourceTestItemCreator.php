<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Creator;

use ArrayIterator;
use EzSystems\EzRecommendationClient\Criteria\Criteria;
use EzSystems\EzRecommendationClient\Tests\Stubs\Item;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;
use EzSystems\EzRecommendationClient\Value\Storage\ItemGroup;
use EzSystems\EzRecommendationClient\Value\Storage\ItemGroupList;
use EzSystems\EzRecommendationClient\Value\Storage\ItemList;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Value\ItemGroupInterface;
use Ibexa\Contracts\Personalization\Value\ItemGroupListInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;
use Ibexa\Contracts\Personalization\Value\ItemTypeInterface;
use Traversable;

final class DataSourceTestItemCreator
{
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
            'body' => sprintf('%s %s %s %s', $itemTypeName, $counter, Item::ITEM_BODY, $language),
            'image' => sprintf(Item::ITEM_IMAGE, $itemTypeIdentifier, $counter),
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