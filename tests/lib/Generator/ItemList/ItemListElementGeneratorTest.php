<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\PersonalizationClient\Generator\ItemList;

use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Generator\Json;
use Ibexa\PersonalizationClient\Generator\ItemList\ItemListElementGenerator;
use Ibexa\PersonalizationClient\Generator\ItemList\ItemListElementGeneratorInterface;
use Ibexa\Tests\PersonalizationClient\Storage\AbstractDataSourceTestCase;

final class ItemListElementGeneratorTest extends AbstractDataSourceTestCase
{
    private ItemListElementGeneratorInterface $itemListElementGenerator;

    /** @var \EzSystems\EzPlatformRest\Output\Generator\Json|\PHPUnit\Framework\MockObject\MockObject */
    private Generator $outputGenerator;

    protected function setUp(): void
    {
        $this->itemListElementGenerator = new ItemListElementGenerator();
        $this->outputGenerator = $this->createMock(Json::class);
    }

    public function testGenerate(): void
    {
        $this->outputGenerator
            ->expects(self::atLeastOnce())
            ->method('startObjectElement')
            ->withConsecutive(
                ['contentList'],
                ['content'],
                ['content'],
            );

        $this->outputGenerator
            ->expects(self::once())
            ->method('startList')
            ->with('content');

        $this->outputGenerator
            ->expects(self::atLeastOnce())
            ->method('valueElement')
            ->withConsecutive(
                ['contentId', '1'],
                ['contentTypeId', '1'],
                ['contentTypeIdentifier', 'article'],
                ['itemTypeName', 'Article'],
                ['language', 'en'],
                ['name', 'Article 1 en'],
                ['body', 'Article 1 body en'],
                ['image', 'public/var/1/2/4/5/article/1'],
                ['contentId', '2'],
                ['contentTypeId', '1'],
                ['contentTypeIdentifier', 'article'],
                ['itemTypeName', 'Article'],
                ['language', 'en'],
                ['name', 'Article 2 en'],
                ['body', 'Article 2 body en'],
                ['image', 'public/var/1/2/4/5/article/2'],
            );

        $this->outputGenerator
            ->expects(self::atLeastOnce())
            ->method('endObjectElement')
            ->withConsecutive(
                ['content'],
                ['content'],
                ['contentList'],
            );

        $this->outputGenerator
            ->expects(self::once())
            ->method('endList')
            ->with('content');

        self::assertEquals(
            $this->outputGenerator,
            $this->itemListElementGenerator->generateElement(
                $this->outputGenerator,
                $this->itemCreator->createTestItemListForEnglishArticles()
            )
        );
    }
}
