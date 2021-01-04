<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Factory;

use EzSystems\EzRecommendationClient\Api\AbstractApi;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;

abstract class AbstractEzRecommendationClientApiFactory
{
    /**
     * @param string $name
     * @param \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface $client
     *
     * @return \EzSystems\EzRecommendationClient\Api\AbstractApi
     */
    abstract public function buildApi(string $name, EzRecommendationClientInterface $client): AbstractApi;
}
