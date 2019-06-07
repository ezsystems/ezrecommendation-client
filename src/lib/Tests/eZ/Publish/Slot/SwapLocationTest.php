<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\eZ\Publish\Slot;

use eZ\Publish\Core\SignalSlot\Signal\LocationService\SwapLocationSignal;

class SwapLocationTest extends AbstractSlotTest
{
    const LOCATION_1_ID = 100;
    const LOCATION_2_ID = 101;

    public function testReceiveSignal()
    {
        $signal = $this->createSignal();

        $this->assertRecommendationServiceIsNotified([
            'swapLocation' => [self::LOCATION_1_ID, self::LOCATION_2_ID],
        ]);

        $this->slot->receive($signal);
    }

    protected function createSignal()
    {
        return new SwapLocationSignal([
            'location1Id' => self::LOCATION_1_ID,
            'location2Id' => self::LOCATION_2_ID,
        ]);
    }

    protected function getSlotClass()
    {
        return 'EzSystems\EzRecommendationClient\eZ\Publish\Slot\SwapLocation';
    }

    protected function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\LocationService\SwapLocationSignal'];
    }
}
