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
        $articleListEn = $this->itemCreator->createTestItemListForEnglishArticles();
        $articleListDe = $this->itemCreator->createTestItemListForGermanArticles();
        $blogListEn = $this->itemCreator->createTestItemListForEnglishBlogPosts();
        $blogListFr = $this->itemCreator->createTestItemListForFrenchBlogPosts();

        $expectedGroupList = $this->itemCreator->createTestItemGroupListForArticlesAndBlogPosts();

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
