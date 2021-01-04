<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\eZ\Publish\Slot;

use eZ\Publish\Core\SignalSlot\Signal\LocationService\HideLocationSignal;

class HideLocationTest extends AbstractSlotTest
{
    const LOCATION_ID = 100;

    public function testReceiveSignal()
    {
        $signal = $this->createSignal();

        $this->assertRecommendationServiceIsNotified([
            'hideLocation' => [self::LOCATION_ID],
        ]);

        $this->slot->receive($signal);
    }

    protected function createSignal()
    {
        return new HideLocationSignal([
            'locationId' => self::LOCATION_ID,
        ]);
    }

    protected function getSlotClass()
    {
        return 'EzSystems\EzRecommendationClient\eZ\Publish\Slot\HideLocation';
    }

    protected function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\LocationService\HideLocationSignal'];
    }
}
