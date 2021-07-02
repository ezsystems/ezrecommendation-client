<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service\Storage;

use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Value\ItemGroupListInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;

/**
 * @internal
 */
interface DataSourceServiceInterface
{
    /**
     * Returns ItemInterface based on item type identifier and language.
     */
    public function getItem(string $identifier, string $language): ?ItemInterface;

    /**
     * Returns collection of ItemInterface matched by CriteriaInterface.
     */
    public function getItems(CriteriaInterface $criteria): ItemListInterface;

    /**
     * Returns collection of ItemGroupInterface matched by CriteriaInterface and grouped.
     */
    public function getGroupedItems(CriteriaInterface $criteria, string $groupBy): ItemGroupListInterface;
}
