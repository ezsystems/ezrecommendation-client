<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Config;

use EzSystems\EzRecommendationClient\Value\Config\EzRecommendationClientCredentials;
use EzSystems\EzRecommendationClient\Value\Parameters;

final class EzRecommendationClientCredentialsResolver extends CredentialsResolver
{
    public function getCredentials(?string $siteAccess = null): ?EzRecommendationClientCredentials
    {
        if (!$this->hasCredentials($siteAccess)) {
            return null;
        }

        return EzRecommendationClientCredentials::fromArray($this->getRequiredCredentials($siteAccess));
    }

    /**
     * @phpstan-return array{
     *  'customerId': ?int,
     *  'licenseKey': ?string,
     * }
     */
    protected function getRequiredCredentials(?string $siteAccess = null): array
    {
        return [
            EzRecommendationClientCredentials::CUSTOMER_ID_KEY => $this->configResolver->getParameter(
                'authentication.customer_id',
                Parameters::NAMESPACE,
                $siteAccess
            ),
            EzRecommendationClientCredentials::LICENSE_KEY_KEY => $this->configResolver->getParameter(
                'authentication.license_key',
                Parameters::NAMESPACE,
                $siteAccess
            ),
        ];
    }
}
