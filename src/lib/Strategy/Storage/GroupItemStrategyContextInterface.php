<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Strategy\Storage;

use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Value\ItemGroupListInterface;

interface GroupItemStrategyContextInterface
{
    /**
     * @throws \EzSystems\EzRecommendationClient\Exception\UnsupportedGroupItemStrategy
     */
    public function getGroupList(CriteriaInterface $criteria, string $groupBy): ItemGroupListInterface;
}