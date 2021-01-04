<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\eZ\Publish\Slot;

use eZ\Publish\Core\SignalSlot\Signal;

class Recover extends PersistenceAwareBase
{
    /**
     * {@inheritdoc}
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\TrashService\RecoverSignal) {
            return;
        }

        $contentIdArray = $this->persistenceHandler
            ->locationHandler()
            ->loadSubtreeIds($signal->newLocationId);

        $relations = $this->persistenceHandler
            ->contentHandler()
            ->loadReverseRelations($signal->contentId);

        foreach ($contentIdArray as $contentId) {
            $this->signalSlotService->updateContent((int) $contentId);
        }

        foreach ($relations as $relation) {
            $this->signalSlotService->updateContent((int) $relation->destinationContentId);
        }
    }
}
