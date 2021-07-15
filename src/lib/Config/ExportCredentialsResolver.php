<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Config;

use EzSystems\EzRecommendationClient\Value\Config\Credentials;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use EzSystems\EzRecommendationClient\Value\ExportMethod;
use EzSystems\EzRecommendationClient\Value\Parameters;

final class ExportCredentialsResolver extends CredentialsResolver
{
    public function getCredentials(?string $siteAccess = null): ?Credentials
    {
        $requiredCredentials = $this->getRequiredCredentials($siteAccess);

        if ($requiredCredentials['method'] === ExportMethod::USER && !$this->hasCredentials($siteAccess)) {
            return null;
        }

        return new ExportCredentials($requiredCredentials);
    }

    /**
     * @phpstan-return array{
     *  'method': ?string,
     *  'login': ?string,
     *  'password': ?string,
     * }
     */
    protected function getRequiredCredentials(?string $siteAccess = null): array
    {
        return [
            'method' => $this->configResolver->getParameter(
                'export.authentication.method',
                Parameters::NAMESPACE,
                $siteAccess
            ),
            'login' => $this->configResolver->getParameter(
                'export.authentication.login',
                Parameters::NAMESPACE,
                $siteAccess
            ),
            'password' => $this->configResolver->getParameter(
                'export.authentication.password',
                Parameters::NAMESPACE,
                $siteAccess
            ),
        ];
    }
}
