<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Config;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Config\ExportCredentialsChecker;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ExportCredentialsCheckerTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    protected function setUp()
    {
        $this->configResolver = $this->getMockBuilder(ConfigResolverInterface::class)->getMock();

        parent::setUp();
    }

    public function testCreateExportCredentialsCheckerInstance()
    {
        $this->assertInstanceOf(ExportCredentialsChecker::class, new ExportCredentialsChecker(
            $this->configResolver,
            new NullLogger()
        ));
    }

    /**
     * Test for getCredentials() method.
     */
    public function testGetCredentialsForAuthenticationMethodUser()
    {
        $credentialsChecker = new ExportCredentialsChecker(
            $this->configResolver,
            new NullLogger()
        );

        $this->assertInstanceOf(ExportCredentials::class, $credentialsChecker->getCredentials());
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

        $credentialsChecker = new ExportCredentialsChecker(
            $this->configResolver,
            new NullLogger()
        );

        $this->assertNull($credentialsChecker->getCredentials());
    }
}
