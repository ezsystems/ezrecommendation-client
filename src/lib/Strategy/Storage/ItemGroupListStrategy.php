<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Strategy\Storage;

use EzSystems\EzRecommendationClient\Exception\UnsupportedGroupItemStrategy;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Value\ItemGroupListInterface;

final class ItemGroupListStrategy implements ItemGroupListStrategyInterface
{
    /** @var iterable<\EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface> */
    private iterable $strategies;

    /** @param iterable<\EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface> $strategies */
    public function __construct(iterable $strategies)
    {
        $this->strategies = $strategies;
    }

    public function getGroupList(CriteriaInterface $criteria, string $groupBy): ItemGroupListInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($groupBy)) {
                return $strategy->getGroupList($criteria);
            }
        }

        throw new UnsupportedGroupItemStrategy($groupBy);
    }
}
