<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Factory;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Api\AbstractApi;
use EzSystems\EzRecommendationClient\Api\AllowedApi;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Exception\BadApiCallException;
use EzSystems\EzRecommendationClient\Exception\InvalidArgumentException;
use EzSystems\EzRecommendationClient\Value\Parameters;

final class EzRecommendationClientApiFactory extends AbstractEzRecommendationClientApiFactory
{
    /** @var \EzSystems\EzRecommendationClient\Api\AllowedApi $allowedApi */
    private $allowedApi;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct(AllowedApi $allowedApi, ConfigResolverInterface $configResolver)
    {
        $this->allowedApi = $allowedApi;
        $this->configResolver = $configResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \EzSystems\EzRecommendationClient\Exception\InvalidArgumentException
     * @throws \EzSystems\EzRecommendationClient\Exception\BadApiCallException
     */
    public function buildApi(string $name, EzRecommendationClientInterface $client): AbstractApi
    {
        if (!array_key_exists($name, $this->allowedApi->getAllowedApi())) {
            throw new InvalidArgumentException(sprintf('Given api key: %s is not found in allowedApi array', $name));
        }

        $api = $this->allowedApi->getAllowedApi()[$name];

        if (!class_exists($api)) {
            throw new BadApiCallException($api);
        }

        $endPoint = $this->getApiEndPoint($name);

        return new $api($client, $endPoint);
    }

    /**
     * @param string $apiName
     *
     * @return string
     */
    private function getApiEndPoint(string $apiName): string
    {
        $parameterName = $this->getApiEndPointParameterName($apiName);

        return $this->configResolver->getParameter($parameterName . '.endpoint', Parameters::NAMESPACE, Parameters::API_SCOPE);
    }

    /**
     * @param string $apiName
     *
     * @return string
     */
    private function getApiEndPointParameterName(string $apiName): string
    {
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $apiName)), '_');
    }
}
