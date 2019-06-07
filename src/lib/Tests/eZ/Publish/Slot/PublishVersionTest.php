<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\eZ\Publish\Slot;

use eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal;
use eZ\Publish\SPI\Persistence\Content\Relation;

class PublishVersionTest extends AbstractPersistenceAwareBaseTest
{
    const CONTENT_ID = 100;

    public function testReceiveSignal()
    {
        $contentHandler = $this->getMockBuilder('\eZ\Publish\SPI\Persistence\Content\Handler')->getMock();
        $contentHandler
            ->expects($this->once())
            ->method('loadReverseRelations')
            ->with(self::CONTENT_ID)
            ->willReturn(array_map(function ($id) {
                return new Relation([
                    'destinationContentId' => $id,
                ]);
            }, $this->getReverseRelationsIds()));

        $this->persistenceHandler
            ->expects($this->once())
            ->method('contentHandler')
            ->willReturn($contentHandler);

        $this->assertRecommendationServiceIsNotified([
            'updateContent' => array_merge($this->getReverseRelationsIds(), [
                self::CONTENT_ID,
            ]),
        ]);

        $this->slot->receive($this->createSignal());
    }

    protected function createSignal()
    {
        return new PublishVersionSignal([
            'contentId' => self::CONTENT_ID,
        ]);
    }

    protected function getSlotClass()
    {
        return 'EzSystems\EzRecommendationClient\eZ\Publish\Slot\PublishVersion';
    }

    protected function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal'];
    }

    private function getReverseRelationsIds()
    {
        return [101, 105, 107];
    }
}
