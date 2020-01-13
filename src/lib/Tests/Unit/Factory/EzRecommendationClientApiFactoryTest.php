<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Unit\Factory;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Api\AllowedApi;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Exception\BadApiCallException;
use EzSystems\EzRecommendationClient\Exception\InvalidArgumentException;
use EzSystems\EzRecommendationClient\Factory\AbstractEzRecommendationClientApiFactory;
use EzSystems\EzRecommendationClient\Factory\EzRecommendationClientApiFactory;
use EzSystems\EzRecommendationClient\Tests\Common\API\APIEndPointClassTest;
use PHPUnit\Framework\TestCase;

class EzRecommendationClientApiFactoryTest extends TestCase
{
    /** @var \EzSystems\EzRecommendationClient\Factory\EzRecommendationClientApiFactory */
    private $apiFactory;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $configResolverMock;

    /** @var \EzSystems\EzRecommendationClient\Api\AllowedApi|\PHPUnit\Framework\MockObject\MockObject */
    private $allowedApi;

    /** @var \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $clientMock;

    public function setUp(): void
    {
        $this->clientMock = $this->createMock(EzRecommendationClientInterface::class);
        $this->configResolverMock = $this->createMock(ConfigResolverInterface::class);
        $this->allowedApi = $this->createMock(AllowedApi::class);
        $this->apiFactory = new EzRecommendationClientApiFactory(
            $this->allowedApi,
            $this->configResolverMock
        );
    }

    public function testCreateEzRecommendationClientApiFactoryInstance()
    {
        $this->assertInstanceOf(
            AbstractEzRecommendationClientApiFactory::class,
            $this->apiFactory
        );
    }

    public function testThrowExceptionWhenInvalidAPIKeyIsGiven()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->apiFactory->buildApi('invalid-api-key', $this->clientMock);
    }

    public function testThrowExceptionWhenAPIClassDoesNotExists()
    {
        $this->expectException(BadApiCallException::class);
        $this->allowedApi
            ->expects($this->atLeastOnce())
            ->method('getAllowedApi')
            ->willReturn([
                'api-name' => 'invalid-api-class',
            ]);

        $this->apiFactory->buildApi('api-name', $this->clientMock);
    }

    /**
     * @dataProvider apiDataProvider
     */
    public function testReturnAPIClass(string $apiName)
    {
        $this->allowedApi
            ->expects($this->atLeastOnce())
            ->method('getAllowedApi')
            ->willReturn([
                'endpoint1' => APIEndPointClassTest::class,
                'endpoint2' => APIEndPointClassTest::class,
                'endpoint3' => APIEndPointClassTest::class,
                'endpoint4' => APIEndPointClassTest::class,
            ]);

        $this->configResolverMock
            ->expects($this->once())
            ->method('getParameter')
            ->willReturn('api.endpoint.uri');

        $this->apiFactory->buildApi($apiName, $this->clientMock);
    }

    public function apiDataProvider(): array
    {
        return [
            ['endpoint1'],
            ['endpoint2'],
            ['endpoint3'],
            ['endpoint4'],
        ];
    }
}
