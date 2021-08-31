<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Personalization\Value;

use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;
use Ibexa\Personalization\Value\Storage\ItemList;
use Ibexa\Tests\Personalization\Creator\DataSourceTestItemCreator;
use Ibexa\Tests\Personalization\Storage\AbstractDataSourceTestCase;
use OutOfBoundsException;

/**
 * @covers \Ibexa\Personalization\Value\Storage\ItemList
 */
final class ItemListTest extends AbstractDataSourceTestCase
{
    public function testThrowExceptionWhenFirstElementNotExists(): void
    {
        $itemList = new ItemList([]);

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Collection is empty');

        $itemList->first();
    }

    /**
     * @dataProvider provideDataForTestFirst
     */
    public function testFirst(ItemListInterface $itemList, ItemInterface $expectedItem): void
    {
        self::assertEquals($expectedItem, $itemList->first());
    }

    /**
     * @phpstan-return iterable<array{
     *  \Ibexa\Contracts\Personalization\Value\ItemListInterface,
     *  \Ibexa\Contracts\Personalization\Value\ItemInterface
     * }>
     */
    public function provideDataForTestFirst(): iterable
    {
        $firstArticle = $this->itemCreator->createTestItem(
            1,
            '1',
            DataSourceTestItemCreator::ARTICLE_TYPE_ID,
            DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
            DataSourceTestItemCreator::ARTICLE_NAME,
            DataSourceTestItemCreator::LANGUAGE_EN,
        );

        $secondArticle = $this->itemCreator->createTestItem(
            2,
            '2',
            DataSourceTestItemCreator::ARTICLE_TYPE_ID,
            DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
            DataSourceTestItemCreator::ARTICLE_NAME,
            DataSourceTestItemCreator::LANGUAGE_EN,
        );

        yield [
            $this->itemCreator->createTestItemList($firstArticle),
            $firstArticle,
        ];
        yield [
            $this->itemCreator->createTestItemList($firstArticle, $secondArticle),
            $firstArticle,
        ];
    }
}
