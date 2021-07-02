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

        $articleListEn = $this->itemCreator->createTestItemList(
            $this->itemCreator->createTestItem(
                1,
                '1',
                ItemType::ARTICLE_IDENTIFIER,
                'Article',
                'en'
            ),
            $this->itemCreator->createTestItem(
                2,
                '2',
                ItemType::ARTICLE_IDENTIFIER,
                'Article',
                'en'
            ),
        );

        $articleListDe = $this->itemCreator->createTestItemList(
            $this->itemCreator->createTestItem(
                1,
                '3',
                ItemType::ARTICLE_IDENTIFIER,
                'Article',
                'de'
            ),
            $this->itemCreator->createTestItem(
                2,
                '4',
                ItemType::ARTICLE_IDENTIFIER,
                'Article',
                'de'
            ),
        );

        $blogListEn = $this->itemCreator->createTestItemList(
            $this->itemCreator->createTestItem(
                1,
                '5',
                ItemType::BLOG_IDENTIFIER,
                'Blog post',
                'en'
            ),
            $this->itemCreator->createTestItem(
                2,
                '6',
                ItemType::BLOG_IDENTIFIER,
                'Blog post',
                'en'
            ),
            $this->itemCreator->createTestItem(
                3,
                '7',
                ItemType::BLOG_IDENTIFIER,
                'Blog post',
                'en'
            ),
        );

        $blogListFr = $this->itemCreator->createTestItemList(
            $this->itemCreator->createTestItem(
                1,
                '8',
                ItemType::BLOG_IDENTIFIER,
                'Blog post',
                'fr'
            ),
            $this->itemCreator->createTestItem(
                2,
                '9',
                ItemType::BLOG_IDENTIFIER,
                'Blog post',
                'fr'
            ),
            $this->itemCreator->createTestItem(
                3,
                '10',
                ItemType::BLOG_IDENTIFIER,
                'Blog post',
                'fr'
            ),
        );

        $expectedGroupList = $this->itemCreator->createTestItemGroupList(
            $this->itemCreator->createTestItemGroup(
                ItemType::ARTICLE_IDENTIFIER . '_' . 'en',
                $articleListEn
            ),
            $this->itemCreator->createTestItemGroup(
                ItemType::ARTICLE_IDENTIFIER . '_' . 'de',
                $articleListDe
            ),
            $this->itemCreator->createTestItemGroup(
                ItemType::BLOG_IDENTIFIER . '_' . 'en',
                $blogListEn
            ),
            $this->itemCreator->createTestItemGroup(
                ItemType::BLOG_IDENTIFIER . '_' . 'fr',
                $blogListFr
            ),
        );

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
