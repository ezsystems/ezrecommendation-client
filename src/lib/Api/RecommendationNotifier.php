<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Api;

use EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface;
use EzSystems\EzRecommendationClient\Value\Config\RecommendationNotifierCredentials;
use Symfony\Component\HttpFoundation\Request;

class RecommendationNotifier extends AbstractApi
{
    const API_NAME = 'recommendationNotifier';

    /**
     * {@inheritdoc}
     */
    public function getRawEndPointUri(): string
    {
        return '%s/api/%s/items';
    }

    /**
     * Notifies the Recommendation Service API of one or more repository events.
     *
     * A repository event is defined as an array with three keys:
     * - action: the event name (update, delete)
     * - uri: the event's target, as an absolute HTTP URI to the REST resource
     * - contentTypeId: currently processed ContentType ID
     *
     * @param \EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface $credentialsChecker
     * @param array $events
     *
     * @throws \InvalidArgumentException If provided $events seems to be of wrong type
     * @throws \GuzzleHttp\Exception\RequestException if a request error occurs
     */
    public function notify(CredentialsCheckerInterface $credentialsChecker, array $events): void
    {
        $customerId = $this->client->getCustomerId();

        $data = [
            'json' => [
                'transaction' => null,
                'events' => $events,
            ],
            'auth' => [
                $customerId,
                $this->client->getLicenseKey(),
            ],
        ];

        /** @var RecommendationNotifierCredentials $credentials */
        $credentials = $credentialsChecker->getCredentials();

        $endPointUri = $this->buildEndPointUri([
            rtrim($credentials->getApiEndpoint(), '/'),
            $customerId,
        ]);

        $this->client->sendRequest(Request::METHOD_POST, $endPointUri, $data);
    }
}
