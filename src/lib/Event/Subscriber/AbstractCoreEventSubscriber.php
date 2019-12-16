<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use EzSystems\EzRecommendationClient\Service\NotificationService;

abstract class AbstractCoreEventSubscriber
{
    protected const EVENT_PRIORITY = 10;

    /** @var \EzSystems\EzRecommendationClient\Service\EventNotificationService */
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
}
