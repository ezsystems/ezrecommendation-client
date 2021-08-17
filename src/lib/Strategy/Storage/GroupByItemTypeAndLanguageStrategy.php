<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Strategy\Storage;

use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Storage\DataSourceInterface;
use Ibexa\Contracts\Personalization\Value\ItemGroupListInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;
use Ibexa\Personalization\Criteria\Criteria;
use Ibexa\Personalization\Strategy\Storage\SupportedGroupItemStrategy;
use Ibexa\Personalization\Value\Storage\ItemGroup;
use Ibexa\Personalization\Value\Storage\ItemGroupList;
use Ibexa\Personalization\Value\Storage\ItemList;

final class GroupByItemTypeAndLanguageStrategy implements GroupItemStrategyInterface
{
    private const GROUP_IDENTIFIER = '%s_%s';

    public function getGroupList(DataSourceInterface $source, CriteriaInterface $criteria): ItemGroupListInterface
    {
        $groupedItems = [];

        foreach ($criteria->getItemTypeIdentifiers() as $identifier) {
            foreach ($criteria->getLanguages() as $language) {
                $modifiedCriteria = new Criteria([$identifier], [$language]);
                if ($source->countItems($modifiedCriteria) > 0) {
                    $groupedItems[] = new ItemGroup(
                        sprintf(
                            self::GROUP_IDENTIFIER,
                            $identifier,
                            $language
                        ),
                        $this->getItemList($source->fetchItems($modifiedCriteria))
                    );
                }
            }
        }

        return new ItemGroupList($groupedItems);
    }

    public static function getIndex(): string
    {
        return SupportedGroupItemStrategy::GROUP_BY_ITEM_TYPE_AND_LANGUAGE;
    }

    /**
     * @param iterable<\Ibexa\Contracts\Personalization\Value\ItemInterface> $items
     */
    private function getItemList(iterable $items): ItemListInterface
    {
        $extractedItems = [];

        foreach ($items as $item) {
            $extractedItems[] = $item;
        }

        return new ItemList($extractedItems);
    }
}
