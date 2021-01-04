<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\eZ\Publish\Slot;

use eZ\Publish\Core\SignalSlot\Signal\ContentService\RevealContentSignal;

class RevealContentTest extends AbstractSlotTest
{
    const CONTENT_ID = 100;

    public function testReceiveSignal()
    {
        $signal = $this->createSignal();

        $this->assertRecommendationServiceIsNotified([
            'revealContent' => [self::CONTENT_ID],
        ]);

        $this->slot->receive($signal);
    }

    protected function createSignal()
    {
        return new RevealContentSignal([
            'contentId' => self::CONTENT_ID,
        ]);
    }

    protected function getSlotClass()
    {
        return 'EzSystems\EzRecommendationClient\eZ\Publish\Slot\RevealContent';
    }

    protected function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\ContentService\RevealContentSignal'];
    }
}
