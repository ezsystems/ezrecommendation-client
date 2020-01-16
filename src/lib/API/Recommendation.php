<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\API;

use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\SPI\RecommendationRequest;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

final class Recommendation extends AbstractAPI
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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getRecommendations(RecommendationRequest $request): ?ResponseInterface
    {
        $endPointUri = $this->buildEndPointUri([
                $this->client->getCustomerId(),
                $this->client->getUserIdentifier(),
                $request->scenario,
        ]);

        $queryStringArray = $this->getQueryStringParameters($request);

        return $this->client->sendRequest(Request::METHOD_GET, $endPointUri, [
            'query' => $this->buildQueryStringFromArray($queryStringArray),
        ]);
    }
}
