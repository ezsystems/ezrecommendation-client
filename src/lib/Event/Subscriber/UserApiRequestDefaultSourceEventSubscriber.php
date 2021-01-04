<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Event\UserAPIEvent;
use EzSystems\EzRecommendationClient\Request\UserMetadataRequest;
use EzSystems\EzRecommendationClient\Value\Parameters;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UserApiRequestDefaultSourceEventSubscriber implements EventSubscriberInterface
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /**
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     */
    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UserAPIEvent::UPDATE => ['onRecommendationUpdateUser', 255],
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

        $userAPIEvent->setUserAPIRequest(new UserMetadataRequest([
            'source' => $this->configResolver->getParameter('user_api.default_source', Parameters::NAMESPACE),
        ]));
    }
}
