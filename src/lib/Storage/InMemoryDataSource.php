<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Storage;

use Closure;
use Ibexa\Contracts\PersonalizationClient\Criteria\CriteriaInterface;
use Ibexa\Contracts\PersonalizationClient\Storage\DataSourceInterface;
use Ibexa\Contracts\PersonalizationClient\Value\ItemInterface;
use Ibexa\Contracts\PersonalizationClient\Value\ItemListInterface;

final class InMemoryDataSource implements DataSourceInterface
{
    private ItemListInterface $itemList;

    public function __construct(ItemListInterface $itemList)
    {
        $this->itemList = $itemList;
    }

    public function fetchItems(CriteriaInterface $criteria): iterable
    {
        return $this->itemList
            ->filter($this->getPredicateFromCriteria($criteria))
            ->slice($criteria->getOffset(), $criteria->getLimit());
    }

    public function countItems(CriteriaInterface $criteria): int
    {
        return $this->itemList->filter($this->getPredicateFromCriteria($criteria))->count();
    }

    public function fetchItem(string $id, string $language): ItemInterface
    {
        return $this->itemList->get($id, $language);
    }

    private function getPredicateFromCriteria(CriteriaInterface $criteria): Closure
    {
        return static function (ItemInterface $item) use ($criteria): bool {
            return
                in_array($item->getType()->getIdentifier(), $criteria->getItemTypeIdentifiers(), true)
                && in_array($item->getLanguage(), $criteria->getLanguages(), true);
        };
    }
}
