<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Strategy\Credentials;

use Ibexa\PersonalizationClient\Value\Export\Credentials;

final class NoneMethodStrategy implements ExportCredentialsStrategyInterface
{
    public const EXPORT_AUTH_METHOD_TYPE = 'none';

    public function getCredentials(?string $siteAccess = null): Credentials
    {
        return new Credentials();
    }

    public static function getIndex(): string
    {
        return self::EXPORT_AUTH_METHOD_TYPE;
    }
}
