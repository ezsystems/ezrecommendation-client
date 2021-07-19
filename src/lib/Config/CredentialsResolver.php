<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Config;

use eZ\Publish\Core\MVC\ConfigResolverInterface;

abstract class CredentialsResolver implements CredentialsResolverInterface
{
    protected ConfigResolverInterface $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @return array<string, int|string|null>
     */
    abstract protected function getRequiredCredentials(?string $siteAccess = null): array;

    /**
     * Checks if array returned by getRequiredCredentials method contains valid values retrieved by ConfigResolver.
     */
    public function hasCredentials(?string $siteAccess = null): bool
    {
        foreach ($this->getRequiredCredentials($siteAccess) as $credential) {
            if (empty($credential)) {
                return false;
            }
        }

        return true;
    }
}
