<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class UserHelper
{
    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return string
     */
    public function getCurrentUser()
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') // user has just logged in
            || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') // user has logged in using remember_me cookie
        ) {
            $authenticationToken = $this->tokenStorage->getToken();
            $user = $authenticationToken->getUser();

            if (\is_string($user)) {
                return $user;
            } elseif (method_exists($user, 'getAPIUser')) {
                return $user->getAPIUser()->id;
            }

            return (string) $authenticationToken->getUsername();
        }
    }
}
