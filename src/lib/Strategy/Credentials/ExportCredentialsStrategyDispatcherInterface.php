<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Strategy\Credentials;

use Ibexa\PersonalizationClient\Value\Export\Credentials;

/**
 * @internal
 */
interface ExportCredentialsStrategyDispatcherInterface
{
    public function getCredentials(string $method, ?string $siteAccess = null): Credentials;
}
