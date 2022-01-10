<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Factory;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\API\AbstractAPI;
use EzSystems\EzRecommendationClient\API\AllowedAPI;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Exception\BadAPICallException;
use EzSystems\EzRecommendationClient\Exception\BadAPIEndpointParameterException;
use EzSystems\EzRecommendationClient\Exception\InvalidArgumentException;
use EzSystems\EzRecommendationClient\Value\Parameters;

final class EzRecommendationClientAPIFactory extends AbstractEzRecommendationClientAPIFactory
{
    /** @var \EzSystems\EzRecommendationClient\API\AllowedAPI */
    private $allowedAPI;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct(AllowedAPI $allowedApi, ConfigResolverInterface $configResolver)
    {
        $this->allowedAPI = $allowedApi;
        $this->configResolver = $configResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \EzSystems\EzRecommendationClient\Exception\InvalidArgumentException
     * @throws \EzSystems\EzRecommendationClient\Exception\BadAPICallException
     */
    public function buildAPI(string $name, EzRecommendationClientInterface $client): AbstractAPI
    {
        if (!\array_key_exists($name, $this->allowedAPI->getAllowedAPI())) {
            throw new InvalidArgumentException(sprintf('Given api key: %s is not found in allowedApi array', $name));
        }

        $api = $this->allowedAPI->getAllowedAPI()[$name];

        if (!class_exists($api)) {
            throw new BadAPICallException($api);
        }

        $endPoint = $this->getApiEndPoint($name);

        return new $api($client, $endPoint);
    }

    private function getApiEndPoint(string $apiName): string
    {
        $parameterName = $this->getApiEndPointParameterName($apiName);

        return $this->configResolver->getParameter(
            Parameters::API_SCOPE . '.' . $parameterName . '.endpoint',
            Parameters::NAMESPACE
        );
    }

    /**
     * @throws BadAPIEndpointParameterException
     */
    private function getApiEndPointParameterName(string $apiName): string
    {
        $sanitizedApiName = preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $apiName);

        if (!$sanitizedApiName) {
            throw new BadAPIEndpointParameterException();
        }

        return strtolower(ltrim($sanitizedApiName, '_'));
    }
}
