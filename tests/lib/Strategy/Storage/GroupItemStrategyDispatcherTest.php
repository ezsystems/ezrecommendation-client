<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Strategy\Storage;

use EzSystems\EzRecommendationClient\Exception\UnsupportedGroupItemStrategy;
use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyDispatcher;
use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyDispatcherInterface;
use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface;
use EzSystems\EzRecommendationClient\Strategy\Storage\SupportedGroupItemStrategy;
use EzSystems\EzRecommendationClient\Tests\Storage\AbstractDataSourceTestCase;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;
use Ibexa\Contracts\Personalization\Storage\DataSourceInterface;

final class GroupItemStrategyDispatcherTest extends AbstractDataSourceTestCase
{
    private GroupItemStrategyDispatcherInterface $groupItemStrategyDispatcher;

    /** @var \EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface|\PHPUnit\Framework\MockObject\MockObject */
    private GroupItemStrategyInterface $groupByItemTypeAndLanguages;

    /** @var \Ibexa\Contracts\Personalization\Storage\DataSourceInterface|\PHPUnit\Framework\MockObject\MockObject */
    private DataSourceInterface $dataSource;

    public function setUp(): void
    {
        $this->groupByItemTypeAndLanguages = $this->createMock(GroupItemStrategyInterface::class);
        $strategies = [
            SupportedGroupItemStrategy::GROUP_BY_ITEM_TYPE_AND_LANGUAGE => $this->groupByItemTypeAndLanguages,
        ];

        $this->groupItemStrategyDispatcher = new GroupItemStrategyDispatcher($strategies);
        $this->dataSource = $this->createMock(DataSourceInterface::class);
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
            ->method('getGroupList')
            ->with($this->dataSource, $criteria)
            ->willReturn($expectedGroupList);

        self::assertEquals(
            $expectedGroupList,
            $this->groupItemStrategyDispatcher->getGroupList($this->dataSource, $criteria, $groupBy)
        );
    }

    public function testThrowUnsupportedGroupItemStrategyException(): void
    {
        $criteria = $this->itemCreator->createTestCriteria(
            [
                ItemType::PRODUCT_IDENTIFIER,
            ],
            ['en']
        );

        $this->expectException(UnsupportedGroupItemStrategy::class);
        $this->expectExceptionMessage('Unsupported GroupItemStrategy nonexistent_group_item_strategy');

        $this->groupItemStrategyDispatcher->getGroupList($this->dataSource, $criteria, 'nonexistent_group_item_strategy');
    }
}
