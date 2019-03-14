<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Factory;

use EzSystems\EzRecommendationClient\Api\AbstractApi;
use EzSystems\EzRecommendationClient\Api\AllowedApi;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Exception\BadApiCallException;
use EzSystems\EzRecommendationClient\Exception\InvalidArgumentException;

class EzRecommendationClientApiFactory extends AbstractEzRecommendationClientApiFactory
{
    /** @var array */
    private $allowedApi;

    /**
     * @param \EzSystems\EzRecommendationClient\Api\AllowedApi $allowedApi
     */
    public function __construct(AllowedApi $allowedApi)
    {
        $this->allowedApi = $allowedApi->getAllowedApi();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \EzSystems\EzRecommendationClient\Exception\InvalidArgumentException
     * @throws \EzSystems\EzRecommendationClient\Exception\BadApiCallException
     */
    public function buildApi(string $name, EzRecommendationClientInterface $client): AbstractApi
    {
        if (!array_key_exists($name, $this->allowedApi)) {
            throw new InvalidArgumentException(sprintf('Given api key: %s is not found in allowedApi array', $name));
        }

        $api = $this->allowedApi[$name];

        if (!class_exists($api)) {
            throw new BadApiCallException($api);
        }

        return new $api($client);
    }
}
