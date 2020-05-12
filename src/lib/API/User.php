<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\API;

use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\SPI\UserAPIRequest;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

final class User extends AbstractAPI
{
    const API_NAME = 'user';

    /**
     * {@inheritdoc}
     */
    public function __construct(
        EzRecommendationClientInterface $client,
        string $endPointUri
    ) {
        parent::__construct($client, $endPointUri . '/api/%d/%s/user');
    }

    public function updateUserAttributes(UserAPIRequest $request): ResponseInterface
    {
        $endPointUri = $this->buildEndPointUri([
            $this->client->getCustomerId(),
            $request->source,
        ]);

        return $this->client->sendRequest(Request::METHOD_POST, $endPointUri, [
            'body' => $request->xmlBody,
            'headers' => $this->getHeaders(),
        ]);
    }

    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'text/xml',
            'Authorization' => 'Basic ' . base64_encode($this->client->getCustomerId() . ':' . $this->client->getLicenseKey()),
        ];
    }
}
