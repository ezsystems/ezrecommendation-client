<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value;

use Ibexa\PersonalizationClient\Strategy\Credentials\BasicMethodStrategy;
use Ibexa\PersonalizationClient\Strategy\Credentials\NoneMethodStrategy;
use Ibexa\PersonalizationClient\Strategy\Credentials\UserMethodStrategy;

final class ExportMethod
{
    public const BASIC = BasicMethodStrategy::EXPORT_AUTH_METHOD_TYPE;
    public const USER = UserMethodStrategy::EXPORT_AUTH_METHOD_TYPE;
    public const NONE = NoneMethodStrategy::EXPORT_AUTH_METHOD_TYPE;
}
