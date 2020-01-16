<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Listener;

use EzSystems\EzRecommendationClient\Value\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Creates backup of sessionId in case of sessionId change.
 */
final class SessionBackupListener
{
    /**
     * Creates a backup of current sessionId in case of sessionId change,
     * we need this value to identify user on Recommendation side.
     * Be aware that session is automatically destroyed when user logs off,
     * in this case the new sessionId will be set. This issue can be treated
     * as a later improvement as it's not required by Recommendation to work correctly.
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $session = $event->getRequest()->getSession();

        if ($session === null || !$session->isStarted()) {
            return;
        }

        if (!$event->getRequest()->cookies->has(Session::RECOMMENDATION_SESSION_KEY)) {
            $event->getRequest()->cookies->set(Session::RECOMMENDATION_SESSION_KEY, $session->getId());
        }
    }
}
