<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event;

use EzSystems\EzRecommendationClient\SPI\UserAPIRequest;
use Symfony\Contracts\EventDispatcher\Event;

abstract class UserAPIEvent extends Event
{
    /** @var \EzSystems\EzRecommendationClient\SPI\UserAPIRequest */
    private $request;

    public function getUserAPIRequest(): ?UserAPIRequest
    {
        return $this->request;
    }

    public function setUserAPIRequest(UserAPIRequest $request): void
    {
        $this->request = $request;
    }
}
