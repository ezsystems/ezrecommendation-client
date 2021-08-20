<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Personalization\Storage;

use Ibexa\Contracts\Personalization\Value\ItemListInterface;
use Ibexa\Personalization\Value\Storage\ItemList;
use Ibexa\Tests\Personalization\Creator\DataSourceTestItemCreator;
use PHPUnit\Framework\TestCase;

abstract class AbstractDataSourceTestCase extends TestCase
{
    protected DataSourceTestItemCreator $itemCreator;

    /**
     * @param array<mixed> $data
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->itemCreator = new DataSourceTestItemCreator();
    }

    /**
     * @phpstan-param ?iterable<string, array{
     *  'item_type_id': int,
     *  'item_type_identifier': string,
     *  'item_type_name': string,
     *  'languages': array<string>,
     *  'limit': int,
     * }> $itemsConfig
     */
    public function createItems(?iterable $itemsConfig = null): ItemListInterface
    {
        return ItemList::fromTraversable(
            $this->itemCreator->createTestItems($itemsConfig)
        );
    }
}
