<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Config;

use eZ\Publish\Core\MVC\ConfigResolverInterface;

abstract class CredentialsResolver implements CredentialsResolverInterface
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    abstract protected function getRequiredCredentials(): array;

    /**
     * {@inheritdoc}
     */
    public function hasCredentials(): bool
    {
        $credentials = $this->getRequiredCredentials();

        foreach ($credentials as $credentialKey => $credentialValue) {
            if (empty($credentialValue)) {
                return false;
            }
        }

        return true;
    }
}
