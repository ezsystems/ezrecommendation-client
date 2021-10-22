<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Strategy\Credentials;

use EzSystems\EzRecommendationClient\Value\ExportMethod;
use Ibexa\PersonalizationClient\Value\Export\Credentials;

final class NoneMethodStrategy implements ExportCredentialsStrategyInterface
{
    public function getCredentials(?string $siteAccess = null): Credentials
    {
        return new Credentials();
    }

    public static function getIndex(): string
    {
        return ExportMethod::NONE;
    }
}
