<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Config;

use EzSystems\EzRecommendationClient\Config\CredentialsChecker;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CredentialsCheckerTest extends TestCase
{
    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsChecker|\PHPUnit\Framework\MockObject\MockObject */
    private $credentialsCheckerMock;

    /** @var \Psr\Log\NullLogger|\PHPUnit\Framework\MockObject\MockObject */
    private $loggerMock;

    /** @var array */
    private $credentials;

    /** @var array */
    private $invalidCredentials;

    protected function setUp()
    {
        $this->credentialsCheckerMock = $this->getMockForAbstractClass(
            CredentialsChecker::class,
            [new NullLogger()]
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

    public function testCreateCredentialsCheckerInstance()
    {
        $this->assertInstanceOf(CredentialsChecker::class, $this->credentialsCheckerMock);
    }

    /**
     * Test for hasCredentials() method.
     */
    public function testShouldReturnTrueWhenRequiredCredentialsAreSet()
    {
        $this->credentialsCheckerMock
            ->expects($this->any())
            ->method('getRequiredCredentials')
            ->willReturn($this->credentials);

        $this->assertTrue($this->credentialsCheckerMock->hasCredentials());
    }

    /**
     * Test for hasCredentials() method.
     */
    public function testReturnFalseWhenOneOfRequiredCredentialsAreMissing()
    {
        $this->credentialsCheckerMock
            ->expects($this->any())
            ->method('getRequiredCredentials')
            ->willReturn($this->invalidCredentials);

        $this->assertFalse($this->credentialsCheckerMock->hasCredentials());
    }
}
