<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\API;

use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\SPI\Notification;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

final class Notifier extends AbstractAPI
{
    const API_NAME = 'notifier';

    public function __construct(EzRecommendationClientInterface $client, string $endPointUri)
    {
        parent::__construct($client, $endPointUri . '/api/%s/items');
    }

    public function notify(Notification $notification): ResponseInterface
    {
        $this->client
            ->setLicenseKey($notification->licenseKey)
            ->setCustomerId($notification->customerId);

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($notification->customerId . ':' . $notification->licenseKey),
        ];

        $body = json_encode([
            'transaction' => $notification->transaction ?? null,
            'events' => $notification->events,
        ]);

        $uri = isset($notification->endPointUri)
            ? new Uri($notification->endPointUri)
            : $this->buildEndPointUri([$notification->customerId]);

        return $this->client->sendRequest(Request::METHOD_POST, $uri, [
            'body' => $body,
            'headers' => $headers,
        ]);
    }
}
