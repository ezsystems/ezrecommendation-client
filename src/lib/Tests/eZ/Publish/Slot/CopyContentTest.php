<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\eZ\Publish\Slot;

use eZ\Publish\Core\SignalSlot\Signal\ContentService\CopyContentSignal;

class CopyContentTest extends AbstractSlotTest
{
    const DST_CONTENT_ID = 100;

    public function testReceiveSignal()
    {
        $this->assertRecommendationServiceIsNotified([
            'updateContent' => [self::DST_CONTENT_ID],
        ]);

        $this->slot->receive($this->createSignal());
    }

    protected function createSignal()
    {
        return new CopyContentSignal([
            'dstContentId' => self::DST_CONTENT_ID,
        ]);
    }

    protected function getSlotClass()
    {
        return 'EzSystems\EzRecommendationClient\eZ\Publish\Slot\CopyContent';
    }

    protected function getReceivedSignalClasses()
    {
        return ['eZ\Publish\Core\SignalSlot\Signal\ContentService\CopyContentSignal'];
    }
}
