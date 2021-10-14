<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Strategy\Storage;

use Ibexa\Contracts\PersonalizationClient\Criteria\CriteriaInterface;
use Ibexa\Contracts\PersonalizationClient\Storage\DataSourceInterface;
use Ibexa\Contracts\PersonalizationClient\Value\ItemGroupListInterface;

/**
 * @internal
 *
 * All implementations of this interface need to be tagged with `key` attribute,
 * or have a static method getIndex() which returns index name for service tag.
 */
interface GroupItemStrategyInterface
{
    public function getGroupList(DataSourceInterface $source, CriteriaInterface $criteria): ItemGroupListInterface;
}
