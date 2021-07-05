<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Strategy\Storage;

use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface;
use EzSystems\EzRecommendationClient\Strategy\Storage\ItemGroupListStrategy;
use EzSystems\EzRecommendationClient\Strategy\Storage\ItemGroupListStrategyInterface;
use EzSystems\EzRecommendationClient\Strategy\Storage\SupportedGroupItemStrategy;
use EzSystems\EzRecommendationClient\Tests\Storage\AbstractDataSourceTestCase;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;

final class ItemGroupListStrategyTest extends AbstractDataSourceTestCase
{
    private ItemGroupListStrategyInterface $groupItemStrategy;

    /** @var \EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface|\PHPUnit\Framework\MockObject\MockObject */
    private GroupItemStrategyInterface $groupByItemTypeAndLanguages;

    public function setUp(): void
    {
        $this->groupByItemTypeAndLanguages = $this->createMock(GroupItemStrategyInterface::class);
        $strategies = [
            $this->groupByItemTypeAndLanguages,
        ];

        $this->groupItemStrategy = new ItemGroupListStrategy($strategies);
    }

    public function testGetGroupList(): void
    {
        $criteria = $this->itemCreator->createTestCriteria(
            [
                ItemType::ARTICLE_IDENTIFIER,
                ItemType::BLOG_IDENTIFIER,
            ],
            ['en', 'de', 'fr']
        );
        $groupBy = SupportedGroupItemStrategy::GROUP_BY_ITEM_TYPE_AND_LANGUAGE;
        $expectedGroupList = $this->itemCreator->createTestItemGroupListForArticlesAndBlogPosts();

        $this->groupByItemTypeAndLanguages
            ->expects(self::once())
            ->method('supports')
            ->with($groupBy)
            ->willReturn(true);

        $this->groupByItemTypeAndLanguages
            ->expects(self::once())
            ->method('getGroupList')
            ->with($criteria)
            ->willReturn($expectedGroupList);

        self::assertEquals(
            $expectedGroupList,
            $this->groupItemStrategy->getGroupList($criteria, $groupBy)
        );
    }
}
