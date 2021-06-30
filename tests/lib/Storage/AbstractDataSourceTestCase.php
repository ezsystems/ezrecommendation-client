<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Storage;

use EzSystems\EzRecommendationClient\Exception\ItemNotFoundException;
use EzSystems\EzRecommendationClient\Tests\Creator\DataSourceTestItemCreator;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemList;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Storage\DataSourceInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractDataSourceTestCase extends TestCase
{
    protected DataSourceTestItemCreator $itemCreator;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->itemCreator = new DataSourceTestItemCreator();
    }

    /**
     * @phpstan-param ?iterable<string, array{
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
