<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use eZ\Publish\API\Repository\Events\ObjectState\SetContentStateEvent;
use EzSystems\EzRecommendationClient\Value\EventNotification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ObjectStateEventSubscriber extends AbstractCoreEventSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SetContentStateEvent::class => ['onSetContentState', parent::EVENT_PRIORITY]
        ];
    }

    /**
     * @param \eZ\Publish\API\Repository\Events\ObjectState\SetContentStateEvent $event
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onSetContentState(SetContentStateEvent $event): void
    {
        $this->notificationService->sendNotification(
            __METHOD__, EventNotification::ACTION_UPDATE, $event->getContentInfo()
        );
    }
}
