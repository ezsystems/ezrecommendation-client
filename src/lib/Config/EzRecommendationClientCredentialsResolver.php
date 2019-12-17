<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Config;

use EzSystems\EzRecommendationClient\Value\Config\Credentials;
use EzSystems\EzRecommendationClient\Value\Config\EzRecommendationClientCredentials;
use EzSystems\EzRecommendationClient\Value\Parameters;

final class EzRecommendationClientCredentialsResolver extends CredentialsResolver
{
    /**
     * {@inheritdoc}
     */
    public function getCredentials(): ?Credentials
    {
        if (!$this->hasCredentials()) {
            return null;
        }

        return new EzRecommendationClientCredentials($this->getRequiredCredentials());
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredCredentials(): array
    {
        return [
            'customerId' => $this->configResolver->getParameter('authentication.customer_id', Parameters::NAMESPACE),
            'licenseKey' => $this->configResolver->getParameter('authentication.license_key', Parameters::NAMESPACE),
        ];
    }
}
