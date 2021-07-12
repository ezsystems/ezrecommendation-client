<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Strategy\Storage;

use EzSystems\EzRecommendationClient\Strategy\Storage\GroupByItemTypeAndLanguageStrategy;
use EzSystems\EzRecommendationClient\Strategy\Storage\GroupItemStrategyInterface;
use EzSystems\EzRecommendationClient\Tests\Storage\AbstractDataSourceTestCase;
use EzSystems\EzRecommendationClient\Tests\Stubs\ItemType;
use Ibexa\Contracts\Personalization\Storage\DataSourceInterface;

final class GroupByItemTypeAndLanguageStrategyTest extends AbstractDataSourceTestCase
{
    private GroupItemStrategyInterface $strategy;

    /** @var \Ibexa\Contracts\Personalization\Storage\DataSourceInterface|\PHPUnit\Framework\MockObject\MockObject */
    private DataSourceInterface $dataSource;

    public function setUp(): void
    {
        $this->dataSource = $this->createConfiguredMock(DataSourceInterface::class, [
        ]);
        $this->strategy = new GroupByItemTypeAndLanguageStrategy();
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
        $criteriaArticlesEn = $this->itemCreator->createTestCriteria(
            [ItemType::ARTICLE_IDENTIFIER],
            ['en'],
        );
        $criteriaArticlesDe = $this->itemCreator->createTestCriteria(
            [ItemType::ARTICLE_IDENTIFIER],
            ['de'],
        );
        $criteriaBlogPostsEn = $this->itemCreator->createTestCriteria(
            [ItemType::BLOG_IDENTIFIER],
            ['en'],
        );
        $criteriaBlogPostsFr = $this->itemCreator->createTestCriteria(
            [ItemType::BLOG_IDENTIFIER],
            ['fr'],
        );

        $this->dataSource
            ->expects(self::at(0))
            ->method('countItems')
            ->with($criteriaArticlesEn)
            ->willReturn(2);

        $this->dataSource
            ->expects(self::at(1))
            ->method('fetchItems')
            ->with($criteriaArticlesEn)
            ->willReturn($this->itemCreator->createTestItemListForEnglishArticles());

        $this->dataSource
            ->expects(self::at(2))
            ->method('countItems')
            ->with($criteriaArticlesDe)
            ->willReturn(2);

        $this->dataSource
            ->expects(self::at(3))
            ->method('fetchItems')
            ->with($criteriaArticlesDe)
            ->willReturn($this->itemCreator->createTestItemListForGermanArticles());

        $this->dataSource
            ->expects(self::at(5))
            ->method('countItems')
            ->with($criteriaBlogPostsEn)
            ->willReturn(3);

        $this->dataSource
            ->expects(self::at(6))
            ->method('fetchItems')
            ->with($criteriaBlogPostsEn)
            ->willReturn($this->itemCreator->createTestItemListForEnglishBlogPosts());

        $this->dataSource
            ->expects(self::at(8))
            ->method('countItems')
            ->with($criteriaBlogPostsFr)
            ->willReturn(3);

        $this->dataSource
            ->expects(self::at(9))
            ->method('fetchItems')
            ->with($criteriaBlogPostsFr)
            ->willReturn($this->itemCreator->createTestItemListForFrenchBlogPosts());

        self::assertEquals(
            $this->itemCreator->createTestItemGroupListForArticlesAndBlogPosts(),
            $this->strategy->getGroupList($this->dataSource, $criteria)
        );
    }
}
