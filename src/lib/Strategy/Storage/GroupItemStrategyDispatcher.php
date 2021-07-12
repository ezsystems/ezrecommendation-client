<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Strategy\Storage;

use EzSystems\EzRecommendationClient\Exception\UnsupportedGroupItemStrategy;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Storage\DataSourceInterface;
use Ibexa\Contracts\Personalization\Value\ItemGroupListInterface;
use Traversable;

final class GroupItemStrategyDispatcher implements GroupItemStrategyDispatcherInterface
{
    /** @var iterable<\EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface> */
    private iterable $strategies;

    /** @param iterable<\EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface> $strategies */
    public function __construct(iterable $strategies)
    {
        $this->strategies = $strategies;
    }

    public function getGroupList(
        DataSourceInterface $source,
        CriteriaInterface $criteria,
        string $groupBy
    ): ItemGroupListInterface {
        $strategies = $this->strategies instanceof Traversable
            ? iterator_to_array($this->strategies)
            : $this->strategies;

        if (!isset($strategies[$groupBy])) {
            throw new UnsupportedGroupItemStrategy(
                $groupBy,
                implode(', ', array_keys($strategies))
            );
        }

        return $strategies[$groupBy]->getGroupList($source, $criteria);
    }
}
