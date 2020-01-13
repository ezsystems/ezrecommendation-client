<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Authentication;

/**
 * This interface is to be implemented by authenticator classes.
 * Authenticators are meant to be used to run authentication programmatically.
 */
interface AuthenticatorInterface
{
    public function authenticate(): bool;
}
