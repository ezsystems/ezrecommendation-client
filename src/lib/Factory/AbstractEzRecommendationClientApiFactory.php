<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Factory;

use EzSystems\EzRecommendationClient\Api\AbstractApi;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;

abstract class AbstractEzRecommendationClientApiFactory
{
    abstract public function buildApi(string $name, EzRecommendationClientInterface $client): AbstractApi;
}
