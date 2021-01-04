<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\eZ\Publish\Slot;

use eZ\Publish\SPI\Persistence\Handler as PersistenceHandlerInterface;
use EzSystems\EzRecommendationClient\Service\SignalSlotServiceInterface;

abstract class PersistenceAwareBase extends Base
{
    /** @var \eZ\Publish\SPI\Persistence\Handler */
    protected $persistenceHandler;

    /**
     * @param \eZ\Publish\SPI\Persistence\Handler $signalSlotService
     * @param \EzSystems\EzRecommendationClient\Service\SignalSlotServiceInterface $persistenceHandler
     */
    public function __construct(SignalSlotServiceInterface $signalSlotService, PersistenceHandlerInterface $persistenceHandler)
    {
        parent::__construct($signalSlotService);
        $this->persistenceHandler = $persistenceHandler;
    }
}
