<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Service\Storage;

use Ibexa\Contracts\PersonalizationClient\Criteria\CriteriaInterface;
use Ibexa\Contracts\PersonalizationClient\Value\ItemGroupListInterface;
use Ibexa\Contracts\PersonalizationClient\Value\ItemInterface;
use Ibexa\Contracts\PersonalizationClient\Value\ItemListInterface;

/**
 * @internal
 */
interface DataSourceServiceInterface
{
    /**
     * Returns ItemInterface based on item type identifier and language.
     *
     * @throws \EzSystems\EzRecommendationClient\Exception\ItemNotFoundException
     */
    public function getItem(string $identifier, string $language): ItemInterface;

    /**
     * Returns collection of ItemInterface matched by CriteriaInterface.
     */
    public function getItems(CriteriaInterface $criteria): ItemListInterface;

    /**
     * Returns collection of ItemGroupInterface matched by CriteriaInterface and grouped.
     */
    public function getGroupedItems(CriteriaInterface $criteria, string $groupBy): ItemGroupListInterface;
}
