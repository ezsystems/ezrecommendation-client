<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service;

use EzSystems\EzRecommendationClient\Helper\SessionHelper;
use EzSystems\EzRecommendationClient\Helper\UserHelper;
use EzSystems\EzRecommendationClient\Value\Session;

class UserService implements UserServiceInterface
{
    /** @var \EzSystems\EzRecommendationClient\Helper\UserHelper */
    private $userHelper;

    /** @var \EzSystems\EzRecommendationClient\Helper\SessionHelper */
    private $sessionHelper;

    /**
     * @param \EzSystems\EzRecommendationClient\Helper\UserHelper $userHelper
     * @param \EzSystems\EzRecommendationClient\Helper\SessionHelper $sessionHelper
     */
    public function __construct(UserHelper $userHelper, SessionHelper $sessionHelper)
    {
        $this->userHelper = $userHelper;
        $this->sessionHelper = $sessionHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserIdentifier(): string
    {
        $userIdentifier = $this->userHelper->getCurrentUser();

        if (!$userIdentifier) {
            $userIdentifier = $this->sessionHelper->getAnonymousSessionId(Session::RECOMMENDATION_SESSION_KEY);
        }

        return (string) $userIdentifier;
    }
}
