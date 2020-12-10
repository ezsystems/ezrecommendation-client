<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Config;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Config\EzRecommendationClientCredentialsResolver;
use EzSystems\EzRecommendationClient\Value\Config\EzRecommendationClientCredentials;
use PHPUnit\Framework\TestCase;

class EzRecommendationClientCredentialsResolverTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    protected function setUp(): void
    {
        $this->configResolver = $this->getMockBuilder(ConfigResolverInterface::class)->getMock();

        parent::setUp();
    }

    public function testCreateEzRecommendationClientCredentialsResolverInstance()
    {
        $this->assertInstanceOf(EzRecommendationClientCredentialsResolver::class, new EzRecommendationClientCredentialsResolver(
            $this->configResolver,
        ));
    }

    /**
     * Test for getCredentials() method.
     */
    public function testReturnGetEzRecommendationClientCredentials()
    {
        $this->configResolver
            ->expects($this->at(0))
            ->method('getParameter')
            ->with('authentication.customer_id', 'ezrecommendation')
            ->willReturn(12345);

        $this->configResolver
            ->expects($this->at(1))
            ->method('getParameter')
            ->with('authentication.license_key', 'ezrecommendation')
            ->willReturn('12345-12345-12345-12345');

        $credentialsResolver = new EzRecommendationClientCredentialsResolver(
            $this->configResolver,
        );

        $this->assertInstanceOf(EzRecommendationClientCredentials::class, $credentialsResolver->getCredentials());
    }

    /**
     * Test for getCredentials() method.
     */
    public function testReturnNullWhenCredentialsAreNotSet()
    {
        $credentialsResolver = new EzRecommendationClientCredentialsResolver(
            $this->configResolver,
        );

        $this->assertNull($credentialsResolver->getCredentials());
    }
}
