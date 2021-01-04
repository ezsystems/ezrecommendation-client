<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionHelper
{
    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface */
    private $session;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function getAnonymousSessionId(string $sessionKey): string
    {
        $sessionId = $this->session->getId();
        $this->session->set($sessionKey, $sessionId);

        return $sessionId;
    }
}
