<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\API;

use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use Symfony\Component\HttpFoundation\Request;

final class EventTracking extends AbstractAPI
{
    const API_NAME = 'eventTracking';

    /**
     * {@inheritdoc}
     */
    public function __construct(EzRecommendationClientInterface $client, string $recommendationEndPoint)
    {
        parent::__construct($client, $recommendationEndPoint . '/api/%d/rendered/%s/%d/');
    }

    public function sendNotificationPing(string $outputContentTypeId): void
    {
        $endPointUri = $this->buildEndPointUri([
                $this->client->getCustomerId(),
                $this->client->getUserIdentifier(),
                $outputContentTypeId,
        ]);

        $this->client->sendRequest(Request::METHOD_GET, $endPointUri);
    }
}
