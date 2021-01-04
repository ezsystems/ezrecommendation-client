<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Config;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Config\EzRecommendationClientCredentialsChecker;
use EzSystems\EzRecommendationClient\Value\Config\EzRecommendationClientCredentials;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class EzRecommendationClientCredentialsCheckerTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    protected function setUp()
    {
        $this->configResolver = $this->getMockBuilder(ConfigResolverInterface::class)->getMock();

        parent::setUp();
    }

    public function testCreateEzRecommendationClientCredentialsCheckerInstance()
    {
        $this->assertInstanceOf(EzRecommendationClientCredentialsChecker::class, new EzRecommendationClientCredentialsChecker(
            $this->configResolver,
            new NullLogger()
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

        $credentialsChecker = new EzRecommendationClientCredentialsChecker(
            $this->configResolver,
            new NullLogger()
        );

        $this->assertInstanceOf(EzRecommendationClientCredentials::class, $credentialsChecker->getCredentials());
    }

    /**
     * Test for getCredentials() method.
     */
    public function testReturnNullWhenCredentialsAreNotSet()
    {
        $credentialsChecker = new EzRecommendationClientCredentialsChecker(
            $this->configResolver,
            new NullLogger()
        );

        $this->assertNull($credentialsChecker->getCredentials());
    }
}
