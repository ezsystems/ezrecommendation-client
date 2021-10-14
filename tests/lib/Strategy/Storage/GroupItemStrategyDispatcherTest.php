<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\PersonalizationClient\Strategy\Storage;

use EzSystems\EzRecommendationClient\Exception\UnsupportedGroupItemStrategy;
use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyDispatcher;
use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyDispatcherInterface;
use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface;
use Ibexa\Contracts\PersonalizationClient\Storage\DataSourceInterface;
use Ibexa\PersonalizationClient\Strategy\Storage\SupportedGroupItemStrategy;
use Ibexa\Tests\PersonalizationClient\Creator\DataSourceTestItemCreator;
use Ibexa\Tests\PersonalizationClient\Storage\AbstractDataSourceTestCase;

final class GroupItemStrategyDispatcherTest extends AbstractDataSourceTestCase
{
    private GroupItemStrategyDispatcherInterface $groupItemStrategyDispatcher;

    /** @var \EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface|\PHPUnit\Framework\MockObject\MockObject */
    private GroupItemStrategyInterface $groupByItemTypeAndLanguages;

    /** @var \Ibexa\Contracts\PersonalizationClient\Storage\DataSourceInterface|\PHPUnit\Framework\MockObject\MockObject */
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
                DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
                DataSourceTestItemCreator::BLOG_TYPE_IDENTIFIER,
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
                DataSourceTestItemCreator::PRODUCT_TYPE_IDENTIFIER,
            ],
            ['en']
        );

        $exceptionMessage = sprintf(
            'Unsupported GroupItemStrategy: %s. Supported strategies: %s',
            'nonexistent_group_item_strategy',
            SupportedGroupItemStrategy::GROUP_BY_ITEM_TYPE_AND_LANGUAGE
        );
        $this->expectException(UnsupportedGroupItemStrategy::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->groupItemStrategyDispatcher->getGroupList($this->dataSource, $criteria, 'nonexistent_group_item_strategy');
    }
}
