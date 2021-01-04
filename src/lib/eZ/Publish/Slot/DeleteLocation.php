<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\eZ\Publish\Slot;

use eZ\Publish\Core\SignalSlot\Signal;

class DeleteLocation extends Base
{
    /**
     * {@inheritdoc}
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\LocationService\DeleteLocationSignal) {
            return;
        }

        // @todo what about subtree ?
        $this->signalSlotService->deleteContent((int) $signal->contentId);
    }
}
