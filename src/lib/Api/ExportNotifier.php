<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Api;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;

class ExportNotifier extends AbstractApi
{
    const API_NAME = 'exportNotifier';

    /**
     * {@inheritdoc}
     */
    public function getRawEndPointUri(): string
    {
        return $this->endPointUri;
    }

    /**
     * @param array $urls
     * @param array $options
     * @param array $securedDirCredentials
     *
     * @return \Psr\Http\Message\StreamInterface|null
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendRecommendationResponse(array $urls, array $options, array $securedDirCredentials): ?StreamInterface
    {
        $this->setEndPointUri($options['webHook']);

        $events = [];

        foreach ($urls as $contentTypeId => $languages) {
            foreach ($languages as $lang => $contentTypeInfo) {
                $event = [
                    'action' => 'FULL',
                    'format' => 'EZ',
                    'contentTypeId' => $contentTypeId,
                    'contentTypeName' => $contentTypeInfo['contentTypeName'],
                    'lang' => $lang,
                    'uri' => $contentTypeInfo['urlList'],
                    'credentials' => $securedDirCredentials ?? null,
                ];

                $events[] = $event;
            }
        }

        $this->client
            ->setLicenseKey($options['licenseKey'])
            ->setCustomerId((int) $options['customerId']);

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($options['customerId'] . ':' . $options['licenseKey']),
        ];

        $body = json_encode([
            'transaction' => $options['transaction'],
            'events' => $events,
        ]);

        $response = $this->client->sendRequest(Request::METHOD_POST, $this->buildEndPointUri(), [
            'body' => $body,
            'headers' => $headers,
        ]);

        return $response ? $response->getBody() : null;
    }
}
