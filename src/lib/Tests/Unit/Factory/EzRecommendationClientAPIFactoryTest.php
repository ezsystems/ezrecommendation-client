<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Unit\Factory;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\API\AllowedAPI;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Exception\BadAPICallException;
use EzSystems\EzRecommendationClient\Exception\InvalidArgumentException;
use EzSystems\EzRecommendationClient\Factory\AbstractEzRecommendationClientAPIFactory;
use EzSystems\EzRecommendationClient\Factory\EzRecommendationClientAPIFactory;
use EzSystems\EzRecommendationClient\Tests\Common\API\APIEndPointClassTest;
use PHPUnit\Framework\TestCase;

class EzRecommendationClientAPIFactoryTest extends TestCase
{
    /** @var \EzSystems\EzRecommendationClient\Factory\EzRecommendationClientAPIFactory */
    private $apiFactory;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $configResolverMock;

    /** @var \EzSystems\EzRecommendationClient\API\AllowedAPI|\PHPUnit\Framework\MockObject\MockObject */
    private $allowedAPI;

    /** @var \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $clientMock;

    public function setUp(): void
    {
        $this->clientMock = $this->createMock(EzRecommendationClientInterface::class);
        $this->configResolverMock = $this->createMock(ConfigResolverInterface::class);
        $this->allowedAPI = $this->createMock(AllowedAPI::class);
        $this->apiFactory = new EzRecommendationClientAPIFactory(
            $this->allowedAPI,
            $this->configResolverMock
        );
    }

    public function testCreateEzRecommendationClientApiFactoryInstance()
    {
        $this->assertInstanceOf(
            AbstractEzRecommendationClientAPIFactory::class,
            $this->apiFactory
        );
    }

    public function testThrowExceptionWhenInvalidAPIKeyIsGiven()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->apiFactory->buildAPI('invalid-api-key', $this->clientMock);
    }

    public function testThrowExceptionWhenAPIClassDoesNotExists()
    {
        $this->expectException(BadAPICallException::class);
        $this->allowedAPI
            ->expects($this->atLeastOnce())
            ->method('getAllowedApi')
            ->willReturn([
                'api-name' => 'invalid-api-class',
            ]);

        $this->apiFactory->buildAPI('api-name', $this->clientMock);
    }

    /**
     * @dataProvider apiDataProvider
     */
    public function testReturnAPIClass(string $apiName)
    {
        $this->allowedAPI
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

        $this->apiFactory->buildAPI($apiName, $this->clientMock);
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
