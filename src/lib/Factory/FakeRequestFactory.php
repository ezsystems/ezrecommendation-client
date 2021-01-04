<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Factory;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FakeRequestFactory implements RequestFactoryInterface
{
    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function createRequest(): Request
    {
        if (!$this->session->isStarted()) {
            $this->session->start();
        }

        $request = Request::createFromGlobals();
        $request->setSession($this->session);

        return $request;
    }
}
