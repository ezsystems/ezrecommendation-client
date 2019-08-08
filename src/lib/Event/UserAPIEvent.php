<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event;

use EzSystems\EzRecommendationClient\SPI\UserAPIRequest;
use Symfony\Component\EventDispatcher\Event;

class UserAPIEvent extends Event
{
    /** @var \EzSystems\EzRecommendationClient\SPI\UserAPIRequest */
    private $request;

    const UPDATE = 'recommendation.update_user';
    const FETCH = 'recommendation.fetch_user';

    /**
     * @return \EzSystems\EzRecommendationClient\SPI\UserAPIRequest|null
     */
    public function getUserAPIRequest(): ?UserAPIRequest
    {
        return $this->request;
    }

    /**
     * @param \EzSystems\EzRecommendationClient\SPI\UserAPIRequest $request
     */
    public function setUserAPIRequest(UserAPIRequest $request): void
    {
        $this->request = $request;
    }
}
