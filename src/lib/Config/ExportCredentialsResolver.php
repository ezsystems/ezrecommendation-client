<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
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
    /** @var string */
    private $method;

    /**
     * {@inheritdoc}
     */
    public function getCredentials(?string $siteAccess = null): ?Credentials
    {
        $this->method = $this->configResolver->getParameter(
            'export.authentication.method',
            Parameters::NAMESPACE,
            $siteAccess
        );

        if ($this->method === ExportMethod::USER && !$this->hasCredentials($siteAccess)) {
            return null;
        }

        return new ExportCredentials($this->getRequiredCredentials($siteAccess));
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredCredentials(?string $siteAccess = null): array
    {
        return [
            'method' => $this->method,
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
