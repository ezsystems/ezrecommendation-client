<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Api;

use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Value\RecommendationMetadata;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

class Recommendation extends AbstractApi
{
    const API_NAME = 'recommendation';

    /**
     * {@inheritdoc}
     */
    public function __construct(
        EzRecommendationClientInterface $client,
        string $recommendationEndPoint
    ) {
        parent::__construct($client, $recommendationEndPoint . '/api/v2/%d/%s/%s');
    }

    /**
     * @param \EzSystems\EzRecommendationClient\Value\RecommendationMetadata $recommendationMetadata
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getRecommendations(RecommendationMetadata $recommendationMetadata): ?ResponseInterface
    {
        $endPointUri = $this->buildEndPointUri([
                $this->client->getCustomerId(),
                $this->client->getUserIdentifier(),
                $recommendationMetadata->scenario,
        ]);

        $queryStringArray = $this->getQueryStringParameters($recommendationMetadata);

        return $this->client->sendRequest(Request::METHOD_GET, $endPointUri, [
            'query' => $this->buildQueryStringFromArray($queryStringArray),
        ]);
    }
}
