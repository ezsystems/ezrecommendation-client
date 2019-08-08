<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use EzSystems\EzRecommendationClient\Event\UserAPIEvent;
use EzSystems\EzRecommendationClient\Request\UserMetadataRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UserApiRequestDefaultSourceEventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UserAPIEvent::UPDATE => ['onRecommendationUpdateUser', 128],
        ];
    }

    /**
     * @param \EzSystems\EzRecommendationClient\Event\UserAPIEvent $userAPIEvent
     */
    public function onRecommendationUpdateUser(UserAPIEvent $userAPIEvent): void
    {
        if ($userAPIEvent->getUserAPIRequest()) {
            return;
        }

        $userAPIEvent->setUserAPIRequest(new UserMetadataRequest(['source' => 'default']));
    }
}
