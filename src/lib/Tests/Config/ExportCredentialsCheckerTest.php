<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Config;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Config\ExportCredentialsResolver;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ExportCredentialsResolverTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    protected function setUp()
    {
        $this->configResolver = $this->getMockBuilder(ConfigResolverInterface::class)->getMock();

        parent::setUp();
    }

    public function testCreateExportCredentialsResolverInstance()
    {
        $this->assertInstanceOf(ExportCredentialsResolver::class, new ExportCredentialsResolver(
            $this->configResolver,
            new NullLogger()
        ));
    }

    /**
     * Test for getCredentials() method.
     */
    public function testGetCredentialsForAuthenticationMethodUser()
    {
        $credentialsResolver = new ExportCredentialsResolver(
            $this->configResolver,
            new NullLogger()
        );

        $this->assertInstanceOf(ExportCredentials::class, $credentialsResolver->getCredentials());
    }

    /**
     * Test for getCredentials() method.
     */
    public function testReturnNullWhenMethodIsUserAndHasCredentialsIsFalse()
    {
        $this->configResolver
            ->expects($this->at(0))
            ->method('getParameter')
            ->with('export.authentication.method', 'ezrecommendation')
            ->willReturn('user');

        $credentialsResolver = new ExportCredentialsResolver(
            $this->configResolver,
            new NullLogger()
        );

        $this->assertNull($credentialsResolver->getCredentials());
    }
}
