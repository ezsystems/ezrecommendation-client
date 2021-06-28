<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Storage;

use ArrayIterator;
use EzSystems\EzRecommendationClient\Tests\Stubs\Criteria;
use EzSystems\EzRecommendationClient\Tests\Stubs\Item;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemList;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;
use Ibexa\Contracts\Personalization\Value\ItemTypeInterface;
use PHPUnit\Framework\TestCase;
use Traversable;

abstract class AbstractDataSourceTestCase extends TestCase
{
    /**
     * @phpstan-param array<string, array{
     *  'item_type_identifier': string,
     *  'item_type_name': string,
     *  'languages': array,
     *  'limit': int,
     * }> $testItemsConfig
     *
     * @return Traversable<ItemInterface>
     */
    protected function getDataSourceTestItems(iterable $testItemsConfig): Traversable
    {
        $items = [];

        foreach ($this->createTestItems($testItemsConfig) as $itemGroup) {
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
     * @return iterable<int, iterable<ItemInterface>>
     */
    protected function createTestItems(iterable $testItemsConfig): iterable
    {
        $items = [];
        $counter = 1;

        foreach ($testItemsConfig as $testItem) {
            $createdItems = $this->createTestItemsForGivenLanguages(
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
     * @return array<ItemInterface>
     */
    protected function createTestItemsForGivenLanguages(
        int $id,
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

    protected function createTestItem(
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

    protected function createTestItemType(string $identifier, string $name): ItemTypeInterface
    {
        return new ItemType(
            $identifier,
            $name
        );
    }

    /**
     * @return array<string, string|int|array>
     */
    protected function createTestItemAttributes(
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
    protected function createTestCriteria(
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

    protected function createTestEmptyItemList(): ItemListInterface
    {
        return new ItemList([]);
    }

    protected function createTestItemList(ItemInterface ...$items): ItemListInterface
    {
        return new ItemList($items);
    }
}
