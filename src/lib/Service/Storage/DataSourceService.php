<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Personalization\Service\Storage;

use EzSystems\EzRecommendationClient\Exception\ItemNotFoundException;
use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyDispatcherInterface;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Value\ItemGroupListInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;
use Ibexa\Personalization\Value\Storage\ItemGroupList;
use Ibexa\Personalization\Value\Storage\ItemList;

final class DataSourceService implements DataSourceServiceInterface
{
    /** @var iterable<\Ibexa\Contracts\Personalization\Storage\DataSourceInterface> */
    private iterable $sources;

    private GroupItemStrategyDispatcherInterface $groupItemStrategyDispatcher;

    /** @param iterable<\Ibexa\Contracts\Personalization\Storage\DataSourceInterface> $sources */
    public function __construct(
        iterable $sources,
        GroupItemStrategyDispatcherInterface $groupItemStrategyDispatcher
    ) {
        $this->sources = $sources;
        $this->groupItemStrategyDispatcher = $groupItemStrategyDispatcher;
    }

    public function getItem(string $identifier, string $language): ItemInterface
    {
        foreach ($this->sources as $source) {
            try {
                return $source->fetchItem($identifier, $language);
            } catch (ItemNotFoundException $exception) {
                /** ItemNotFoundException will be thrown if item will not be find in any of data sources */
            }
        }

        throw new ItemNotFoundException($identifier, $language);
    }

    public function getItems(CriteriaInterface $criteria): ItemListInterface
    {
        $items = [];

        foreach ($this->sources as $source) {
            if ($source->countItems($criteria) > 0) {
                foreach ($source->fetchItems($criteria) as $item) {
                    $items[] = $item;
                }
            }
        }

        return new ItemList($items);
    }

    public function getGroupedItems(
        CriteriaInterface $criteria,
        string $groupBy
    ): ItemGroupListInterface {
        $groups = [];

        foreach ($this->sources as $source) {
            foreach ($this->groupItemStrategyDispatcher->getGroupList($source, $criteria, $groupBy)->getGroups() as $group) {
                $groups[] = $group;
            }
        }

        return new ItemGroupList($groups);
    }
}
