<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\eZ\Publish\Slot;

use eZ\Publish\Core\SignalSlot\Slot as BaseSlot;
use EzSystems\EzRecommendationClient\Service\SignalSlotServiceInterface;

abstract class Base extends BaseSlot
{
    /** @var \EzSystems\EzRecommendationClient\Service\SignalSlotServiceInterface */
    protected $signalSlotService;

    /**
     * @param \EzSystems\EzRecommendationClient\Service\SignalSlotServiceInterface $signalSlotService
     */
    public function __construct(SignalSlotServiceInterface $signalSlotService)
    {
        $this->signalSlotService = $signalSlotService;
    }
}
