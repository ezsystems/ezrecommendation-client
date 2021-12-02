<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use eZ\Publish\Core\MVC\Symfony\Security\UserInterface;
use Symfony\Component\Security\Core\Security;

final class UserHelper
{
    /** @var \Symfony\Component\Security\Core\Security */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException
     */
    public function getCurrentUser(): ?string
    {
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY') // user has just logged in
            && !$this->security->isGranted('IS_AUTHENTICATED_REMEMBERED') // user has logged in using remember_me cookie
        ) {
            return null;
        }

        $user = $this->security->getUser();
        if (null === $user) {
            return null;
        }

        if ($user instanceof UserInterface) {
            return (string) $user->getAPIUser()->id;
        }

        return $user->getUserIdentifier();
    }
}
