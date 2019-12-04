<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Config;

use EzSystems\EzRecommendationClient\Value\Config\Credentials;

interface CredentialsResolverInterface
{
    /**
     * Returns object with credentials data.
     *
     * @return \EzSystems\EzRecommendationClient\Value\Config\Credentials|null
     */
    public function getCredentials(): ?Credentials;

    /**
     * @return bool
     */
    public function hasCredentials(): bool;
}
