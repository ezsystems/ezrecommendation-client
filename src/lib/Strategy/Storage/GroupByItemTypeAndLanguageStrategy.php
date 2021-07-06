<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Strategy\Storage;

use EzSystems\EzRecommendationClient\Criteria\Criteria;
use EzSystems\EzRecommendationClient\Service\Storage\DataSourceServiceInterface;
use EzSystems\EzRecommendationClient\Value\Storage\ItemGroup;
use EzSystems\EzRecommendationClient\Value\Storage\ItemGroupList;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Value\ItemGroupListInterface;

final class GroupByItemTypeAndLanguageStrategy implements GroupItemStrategyInterface
{
    private DataSourceServiceInterface $dataSourceService;

    private const GROUP_IDENTIFIER = '%s_%s';

    public function __construct(DataSourceServiceInterface $dataSourceService)
    {
        $this->dataSourceService = $dataSourceService;
    }

    public function getGroupList(CriteriaInterface $criteria): ItemGroupListInterface
    {
        $groupedItems = [];

        foreach ($criteria->getItemTypeIdentifiers() as $identifier) {
            foreach ($criteria->getLanguages() as $language) {
                $modifiedCriteria = new Criteria([$identifier], [$language]);
                $items = $this->dataSourceService->getItems($modifiedCriteria);

                if ($items->count() > 0) {
                    $groupedItems[] = new ItemGroup(
                        sprintf(
                            self::GROUP_IDENTIFIER,
                            $identifier,
                            $language
                        ),
                        $items
                    );
                }
            }
        }

        return new ItemGroupList($groupedItems);
    }

    public function supports(string $groupBy): bool
    {
        return $groupBy === SupportedGroupItemStrategy::GROUP_BY_ITEM_TYPE_AND_LANGUAGE;
    }
}
