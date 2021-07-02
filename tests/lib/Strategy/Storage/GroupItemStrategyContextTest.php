<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Strategy\Storage;

use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyContext;
use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyContextInterface;
use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface;
use EzSystems\EzRecommendationClient\Tests\Storage\AbstractDataSourceTestCase;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;

final class GroupItemStrategyContextTest extends AbstractDataSourceTestCase
{
    private GroupItemStrategyContextInterface $groupItemStrategy;

    /** @var \EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface|\PHPUnit\Framework\MockObject\MockObject */
    private GroupItemStrategyInterface $groupByItemTypeAndLanguages;

    public function setUp(): void
    {
        $this->groupByItemTypeAndLanguages = $this->createMock(GroupItemStrategyInterface::class);
        $strategies = [
            $this->groupByItemTypeAndLanguages,
        ];

        $this->groupItemStrategy = new GroupItemStrategyContext($strategies);
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
        $groupBy = 'item_type_and_language';
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
