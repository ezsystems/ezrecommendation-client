<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Api;

use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Value\Notification;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

class Notifier extends AbstractApi
{
    const API_NAME = 'notifier';

    /**
     * {@inheritdoc}
     */
    public function __construct(EzRecommendationClientInterface $client, string $endPointUri)
    {
        parent::__construct($client, $endPointUri . '/api/%s/items');
    }

    /**
     * @param \EzSystems\EzRecommendationClient\Value\Notification $notification
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function notify(Notification $notification): ?ResponseInterface
    {
        $this->client
            ->setLicenseKey($notification->licenseKey)
            ->setCustomerId($notification->customerId);

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($notification->customerId . ':' . $notification->licenseKey),
        ];

        $body = json_encode([
            'transaction' => $notification->transaction,
            'events' => $notification->events,
        ]);

        $uri = $notification->endPointUri ? new Uri($notification->endPointUri) : $this->buildEndPointUri([$notification->customerId]);

        return $this->client->sendRequest(Request::METHOD_POST, $uri, [
            'body' => $body,
            'headers' => $headers,
        ]);
    }
}
