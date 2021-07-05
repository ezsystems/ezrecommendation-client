<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service\Storage;

use EzSystems\EzRecommendationClient\Exception\ItemNotFoundException;
use EzSystems\EzRecommendationClient\Strategy\Storage\ItemGroupListStrategyInterface;
use EzSystems\EzRecommendationClient\Value\Storage\ItemList;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Value\ItemGroupListInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;

final class DataSourceService implements DataSourceServiceInterface
{
    /** @var iterable<\Ibexa\Contracts\Personalization\Storage\DataSourceInterface> */
    private iterable $sources;

    private ItemGroupListStrategyInterface $groupItemStrategy;

    /** @param iterable<\Ibexa\Contracts\Personalization\Storage\DataSourceInterface> $sources */
    public function __construct(
        iterable $sources,
        ItemGroupListStrategyInterface $groupItemStrategy
    ) {
        $this->sources = $sources;
        $this->groupItemStrategy = $groupItemStrategy;
    }

    public function getItem(string $identifier, string $language): ?ItemInterface
    {
        foreach ($this->sources as $source) {
            try {
                return $source->fetchItem($identifier, $language);
            } catch (ItemNotFoundException $exception) {
            }
        }

        return null;
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
        return $this->groupItemStrategy->getGroupList($criteria, $groupBy);
    }
}
