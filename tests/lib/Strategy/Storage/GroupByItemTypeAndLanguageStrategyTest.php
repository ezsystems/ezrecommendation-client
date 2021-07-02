<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Strategy\Storage;

use EzSystems\EzRecommendationClient\Service\Storage\DataSourceServiceInterface;
use EzSystems\EzRecommendationClient\Strategy\Storage\GroupByItemTypeAndLanguageStrategy;
use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface;
use EzSystems\EzRecommendationClient\Tests\Storage\AbstractDataSourceTestCase;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;

final class GroupByItemTypeAndLanguageStrategyTest extends AbstractDataSourceTestCase
{
    private GroupItemStrategyInterface $strategy;

    /** @var \EzSystems\EzRecommendationClient\Service\Storage\DataSourceServiceInterface|\PHPUnit\Framework\MockObject\MockObject */
    private DataSourceServiceInterface $dataSourceService;

    public function setUp(): void
    {
        $this->dataSourceService = $this->createMock(DataSourceServiceInterface::class);
        $this->strategy = new GroupByItemTypeAndLanguageStrategy($this->dataSourceService);
    }

    public function testGetGroupList(): void
    {
        $articleListEn = $this->itemCreator->createTestItemList(
            $this->itemCreator->createTestItem(
                1,
                '1',
                ItemType::ARTICLE_IDENTIFIER,
                ItemType::ARTICLE_NAME,
                'en'
            ),
            $this->itemCreator->createTestItem(
                2,
                '2',
                ItemType::ARTICLE_IDENTIFIER,
                ItemType::ARTICLE_NAME,
                'en'
            ),
        );

        $articleListDe = $this->itemCreator->createTestItemList(
            $this->itemCreator->createTestItem(
                1,
                '3',
                ItemType::ARTICLE_IDENTIFIER,
                ItemType::ARTICLE_NAME,
                'de'
            ),
            $this->itemCreator->createTestItem(
                2,
                '4',
                ItemType::ARTICLE_IDENTIFIER,
                ItemType::ARTICLE_NAME,
                'de'
            ),
        );

        $blogListEn = $this->itemCreator->createTestItemList(
            $this->itemCreator->createTestItem(
                1,
                '5',
                ItemType::BLOG_IDENTIFIER,
                ItemType::BLOG_NAME,
                'en'
            ),
            $this->itemCreator->createTestItem(
                2,
                '6',
                ItemType::BLOG_IDENTIFIER,
                ItemType::BLOG_NAME,
                'en'
            ),
            $this->itemCreator->createTestItem(
                3,
                '7',
                ItemType::BLOG_IDENTIFIER,
                ItemType::BLOG_NAME,
                'en'
            ),
        );

        $blogListFr = $this->itemCreator->createTestItemList(
            $this->itemCreator->createTestItem(
                1,
                '8',
                ItemType::BLOG_IDENTIFIER,
                ItemType::BLOG_NAME,
                'fr'
            ),
            $this->itemCreator->createTestItem(
                2,
                '9',
                ItemType::BLOG_IDENTIFIER,
                ItemType::BLOG_NAME,
                'fr'
            ),
            $this->itemCreator->createTestItem(
                3,
                '10',
                ItemType::BLOG_IDENTIFIER,
                ItemType::BLOG_NAME,
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

        $criteria = $this->itemCreator->createTestCriteria(
            [
                ItemType::ARTICLE_IDENTIFIER,
                ItemType::BLOG_IDENTIFIER,
            ],
            ['en', 'de', 'fr']
        );

        $this->dataSourceService
            ->expects(self::at(0))
            ->method('getItems')
            ->with(
                $this->itemCreator->createTestCriteria(
                    [ItemType::ARTICLE_IDENTIFIER],
                    ['en'],
                )
            )
            ->willReturn($articleListEn);

        $this->dataSourceService
            ->expects(self::at(1))
            ->method('getItems')
            ->with(
                $this->itemCreator->createTestCriteria(
                    [ItemType::ARTICLE_IDENTIFIER],
                    ['de'],
                )
            )
            ->willReturn($articleListDe);

        $this->dataSourceService
            ->expects(self::at(3))
            ->method('getItems')
            ->with(
                $this->itemCreator->createTestCriteria(
                    [ItemType::BLOG_IDENTIFIER],
                    ['en'],
                )
            )
            ->willReturn($blogListEn);

        $this->dataSourceService
            ->expects(self::at(5))
            ->method('getItems')
            ->with(
                $this->itemCreator->createTestCriteria(
                    [ItemType::BLOG_IDENTIFIER],
                    ['fr'],
                )
            )
            ->willReturn($blogListFr);

        self::assertEquals(
            $expectedGroupList,
            $this->strategy->getGroupList($criteria)
        );
    }

    public function testSupports(): void
    {
        self::assertTrue($this->strategy->supports('item_type_and_language'));
        self::assertFalse($this->strategy->supports('unsupported'));
    }
}
