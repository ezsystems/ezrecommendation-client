<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\eZ\Publish\Slot;

use eZ\Publish\Core\SignalSlot\Signal\TrashService\RecoverSignal;
use eZ\Publish\SPI\Persistence\Content\Relation;

class RecoverTest extends AbstractPersistenceAwareBaseTest
{
    const CONTENT_ID = 100;
    const LOCATION_ID = 58;

    public function testReceiveSignal()
    {
        $signal = $this->createSignal();

        $locationHandler = $this->getMockBuilder('\eZ\Publish\SPI\Persistence\Content\Location\Handler')->getMock();
        $locationHandler
            ->expects($this->once())
            ->method('loadSubtreeIds')
            ->with($signal->newLocationId)
            ->willReturn($this->getSubtreeIds());

        $contentHandler = $this->getMockBuilder('\eZ\Publish\SPI\Persistence\Content\Handler')->getMock();
        $contentHandler
            ->expects($this->once())
            ->method('loadReverseRelations')
            ->with(self::CONTENT_ID)
            ->willReturn(array_map(static function ($id) {
                return new Relation([
                    'destinationContentId' => $id,
                ]);
            }, $this->getReverseRelationsIds()));

        $this->persistenceHandler
            ->expects($this->any())
            ->method('contentHandler')
            ->willReturn($contentHandler);

        $this->persistenceHandler
            ->expects($this->any())
            ->method('locationHandler')
            ->willReturn($locationHandler);

        $this->assertRecommendationServiceIsNotified([
            'updateContent' => array_merge($this->getReverseRelationsIds(), $this->getSubtreeIds()),
        ]);

        $this->slot->receive($signal);
    }

    protected function createSignal()
    {
        return new RecoverSignal([
            'contentId' => self::CONTENT_ID,
            'newLocationId' => self::LOCATION_ID,
        ]);
    }

    protected function getSlotClass()
    {
        return 'EzSystems\EzRecommendationClient\eZ\Publish\Slot\Recover';
    }

    protected function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\TrashService\RecoverSignal'];
    }

    private function getSubtreeIds()
    {
        return [2016, 2017, 2018];
    }

    private function getReverseRelationsIds()
    {
        return [101, 105, 107];
    }
}
