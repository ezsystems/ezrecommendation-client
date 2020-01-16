<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Factory;

use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class TokenFactory implements TokenFactoryInterface
{
    public function createAnonymousToken(?string $secret = null, ?string $user = null, array $roles = []): AnonymousToken
    {
        return new AnonymousToken(
            $secret ?? 'anonymous',
            $user ?? 'anonymous',
            $roles ?? ['ROLE_ADMINISTRATOR']
        );
    }
}
