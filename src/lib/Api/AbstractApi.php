<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Api;

use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

abstract class AbstractApi
{
    /** @var \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface */
    protected $client;

    /** @var string */
    protected $endPointUri;

    /**
     * @param \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface $client
     */
    public function __construct(EzRecommendationClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return string
     */
    abstract public function getRawEndPointUri(): string;

    /**
     * @param string $endPointUri
     */
    protected function setEndPointUri(string $endPointUri): void
    {
        $this->endPointUri = $endPointUri;
    }

    /**
     * @return string
     */
    protected function getEndPointUri(): string
    {
        return $this->endPointUri;
    }

    /**
     * @param array $endPointParameters
     *
     * @return \Psr\Http\Message\UriInterface
     */
    protected function buildEndPointUri(array $endPointParameters = []): UriInterface
    {
        if (!$endPointParameters) {
            return new Uri($this->getRawEndPointUri());
        }

        return new Uri(vsprintf($this->getRawEndPointUri(), $endPointParameters));
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    protected function buildQueryStringFromArray(array $parameters): string
    {
        $queryString = '';

        foreach ($parameters as $parameterKey => $parameterValue) {
            if (is_array($parameterValue)) {
                $queryString .= $this->buildQueryStringFromArray($parameterValue);
            }

            if (is_string($parameterValue) || is_numeric($parameterValue)) {
                $queryString .= $parameterKey . '=' . (string) $parameterValue;
            }

            if (next($parameters)) {
                $queryString .= '&';
            }
        }

        return $queryString;
    }

    /**
     * @param \EzSystems\EzRecommendationClient\Api\ApiMetadata $metadata
     * @param array $requiredAttributes
     *
     * @return array
     */
    protected function getQueryStringParameters(ApiMetadata $metadata, array $requiredAttributes = []): array
    {
        if ($requiredAttributes) {
            return array_intersect_key($metadata->getMetadataAttributes(), array_flip($requiredAttributes));
        }

        return $metadata->getMetadataAttributes();
    }
}
