<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Strategy\Credentials;

use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use Ibexa\PersonalizationClient\Value\Export\Credentials;

final class UserMethodStrategy implements ExportCredentialsStrategyInterface
{
    public const EXPORT_AUTH_METHOD_TYPE = 'user';

    private CredentialsResolverInterface $credentialsResolver;

    public function __construct(CredentialsResolverInterface $credentialsResolver)
    {
        $this->credentialsResolver = $credentialsResolver;
    }

    public function getCredentials(?string $siteAccess = null): Credentials
    {
        /** @var \EzSystems\EzRecommendationClient\Value\Config\ExportCredentials $credentials */
        $credentials = $this->credentialsResolver->getCredentials($siteAccess);

        return new Credentials($credentials->getLogin(), $credentials->getPassword());
    }

    public static function getIndex(): string
    {
        return self::EXPORT_AUTH_METHOD_TYPE;
    }
}
