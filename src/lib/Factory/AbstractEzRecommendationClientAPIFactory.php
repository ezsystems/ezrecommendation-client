<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Factory;

use EzSystems\EzRecommendationClient\API\AbstractAPI;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;

abstract class AbstractEzRecommendationClientAPIFactory
{
    abstract public function buildAPI(string $name, EzRecommendationClientInterface $client): AbstractAPI;
}
