<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Unit\Client;

use EzSystems\EzRecommendationClient\Api\AbstractApi;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClient;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use EzSystems\EzRecommendationClient\Factory\EzRecommendationClientApiFactory;
use EzSystems\EzRecommendationClient\Tests\Common\API\APIEndPointClassTest;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class EzRecommendationClientTest extends TestCase
{
    /** @var \EzSystems\EzRecommendationClient\Client\EzRecommendationClient */
    private $client;

    /** @var \GuzzleHttp\ClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $guzzleClientMock;

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $credentialsResolverMock;

    /** @var \EzSystems\EzRecommendationClient\Factory\EzRecommendationClientApiFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $apiFactoryMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface */
    private $loggerMock;

    public function setUp(): void
    {
        $this->guzzleClientMock = $this->createMock(ClientInterface::class);
        $this->credentialsResolverMock = $this->createMock(CredentialsResolverInterface::class);
        $this->apiFactoryMock = $this->createMock(EzRecommendationClientApiFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->client = new EzRecommendationClient(
            $this->guzzleClientMock,
            $this->credentialsResolverMock,
            $this->apiFactoryMock,
            $this->loggerMock
        );
    }

    public function testCreateEzRecommendationClientInstance()
    {
        $this->assertInstanceOf(EzRecommendationClientInterface::class, $this->client);
    }

    public function testReturnCalledAPI()
    {
        $this->apiFactoryMock
            ->expects($this->once())
            ->method('buildApi')
            ->with($this->equalTo('api-test'))
            ->willReturn(
                new APIEndPointClassTest(
                    $this->client,
                    'api.endpoint.uri'
                )
            );

        $this->assertInstanceOf(
            AbstractApi::class,
            $this->client->__call('api-test', [])
        );
    }

    public function testReturnFalseWhenCredentialsAreNotSet()
    {
        $this->assertFalse(
            $this->client->hasCredentials()
        );
    }

    public function testSendRequestAndReturnNullWhenCredentialsAreNotSet()
    {
        $this->assertNull(
            $this->client->sendRequest(
                'POST',
                new Uri('http://www.test.local'),
                []
            )
        );
    }

    public function testSendRequestAndReturnResponse()
    {
        $this->client
            ->setCustomerId(12345)
            ->setLicenseKey('12345-12345-12345-12345');

        $this->guzzleClientMock
            ->expects($this->once())
            ->method('request')
            ->willReturn(new Response());

        $this->assertInstanceOf(
            ResponseInterface::class,
            $this->client->sendRequest(
                'POST',
                new Uri('http://www.test.local'),
                []
            )
        );
    }

    public function testReturnAbsoluteUri()
    {
        $this->assertEquals(
            'http://www.test.local',
            $this->client->getAbsoluteUri(
                new Uri(
                    'http://www.test.local'
                )
            )
        );
    }

    public function testReturnHeadersAsString()
    {
        $this->assertEquals(
            'headerParamKey1: headerParam1 | headerParamKey2: headerParam2 | headerParamKey3: headerParam3',
            $this->client->getHeadersAsString([
                'headerParamKey1' => ['headerParam1'],
                'headerParamKey2' => ['headerParam2'],
                'headerParamKey3' => ['headerParam3'],
            ])
        );
    }
}
