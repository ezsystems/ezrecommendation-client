<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Config;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Config\CredentialsResolver;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CredentialsResolverTest extends TestCase
{
    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $credentialsResolverMock;

    /** @var \Psr\Log\NullLogger|\PHPUnit\Framework\MockObject\MockObject */
    private $loggerMock;

    /** @var array */
    private $credentials;

    /** @var array */
    private $invalidCredentials;

    protected function setUp(): void
    {
        $this->credentialsResolverMock = $this->getMockForAbstractClass(
            CredentialsResolver::class,
            [
                $this->getMockBuilder(ConfigResolverInterface::class)->getMock(),
                new NullLogger(),
            ]
        );
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        $this->credentials = [
            'firstCredential' => 'firstCredential',
            'secondCredential' => 'secondCredential',
        ];
        $this->invalidCredentials = [
            'firstCredential' => '',
            'secondCredential' => '',
        ];
    }

    public function testCreateCredentialsResolverInstance()
    {
        $this->assertInstanceOf(CredentialsResolver::class, $this->credentialsResolverMock);
    }

    /**
     * Test for hasCredentials() method.
     */
    public function testShouldReturnTrueWhenRequiredCredentialsAreSet()
    {
        $this->credentialsResolverMock
            ->expects($this->any())
            ->method('getRequiredCredentials')
            ->willReturn($this->credentials);

        $this->assertTrue($this->credentialsResolverMock->hasCredentials());
    }

    /**
     * Test for hasCredentials() method.
     */
    public function testReturnFalseWhenOneOfRequiredCredentialsAreMissing()
    {
        $this->credentialsResolverMock
            ->expects($this->any())
            ->method('getRequiredCredentials')
            ->willReturn($this->invalidCredentials);

        $this->assertFalse($this->credentialsResolverMock->hasCredentials());
    }
}
