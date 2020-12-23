<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Authentication;

use EzSystems\EzRecommendationClient\Authentication\ExportAuthenticator;
use EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface;
use EzSystems\EzRecommendationClient\Helper\FileSystemHelper;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem as BaseFileSystem;
use Symfony\Component\HttpFoundation\ParameterBag;

class ExportAuthenticatorTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\EzSystems\EzRecommendationClient\Helper\FileSystemHelper */
    private $fileSystem;

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $credentialsCheckerMock;

    public function setUp()
    {
        parent::setUp();

        $this->credentialsCheckerMock = $this->getMockBuilder(CredentialsCheckerInterface::class)->getMock();
        $this->requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')->getMock();
        $this->fileSystem = $this->getMockBuilder('EzSystems\EzRecommendationClient\Helper\FileSystemHelper')->disableOriginalConstructor()->getMock();
    }

    public function testAuthenticateWithMethodNone()
    {
        $return = new \stdClass();
        $return->server = new ParameterBag([
            'PHP_AUTH_USER' => 'login',
            'PHP_AUTH_PW' => 'password',
        ]);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->withAnyParameters()
            ->willReturn($return);

        $exportAuthenticator = new ExportAuthenticator(
            $this->credentialsCheckerMock,
            $this->requestStack,
            $this->fileSystem
        );

        $this->credentialsCheckerMock
            ->expects($this->any())
            ->method('getCredentials')
            ->willReturn($this->getExportCredentials('none'));

        $this->assertTrue($exportAuthenticator->authenticate());
    }

    public function testAuthenticateWithMethodUser()
    {
        $return = new \stdClass();
        $return->server = new ParameterBag([
            'PHP_AUTH_USER' => '1111',
            'PHP_AUTH_PW' => 'password',
        ]);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->withAnyParameters()
            ->willReturn($return)
        ;

        $exportAuthenticator = new ExportAuthenticator(
            $this->credentialsCheckerMock,
            $this->requestStack,
            $this->fileSystem
        );

        $this->credentialsCheckerMock
            ->expects($this->any())
            ->method('getCredentials')
            ->willReturn($this->getExportCredentials('user'));

        $this->assertTrue($exportAuthenticator->authenticate());
    }

    public function testAuthenticateWithMethodUserAndWrongCredentials()
    {
        $return = new \stdClass();
        $return->server = new ParameterBag([
            'PHP_AUTH_USER' => '12345',
            'PHP_AUTH_PW' => 'wrong_password',
        ]);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->withAnyParameters()
            ->willReturn($return)
        ;

        $exportAuthenticator = new ExportAuthenticator(
            $this->credentialsCheckerMock,
            $this->requestStack,
            $this->fileSystem
        );

        $this->credentialsCheckerMock
            ->expects($this->any())
            ->method('getCredentials')
            ->willReturn($this->getExportCredentials('user'));

        $this->assertFalse($exportAuthenticator->authenticate());
    }

    public function testAuthenticateByFile()
    {
        $return = new \stdClass();
        $return->server = new ParameterBag([
            'PHP_AUTH_USER' => 'login',
            'PHP_AUTH_PW' => 'password',
        ]);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->withAnyParameters()
            ->willReturn($return)
        ;

        $this->fileSystem
            ->expects($this->once())
            ->method('load')
            ->withAnyParameters()
            ->willReturn('login:5fjgIzboD2FrE')
        ;

        $exportAuthenticator = new ExportAuthenticator(
            $this->credentialsCheckerMock,
            $this->requestStack,
            $this->fileSystem
        );

        $this->assertTrue($exportAuthenticator->authenticateByFile('file'));
    }

    public function testAuthenticateByFileWithWrongCredenrials()
    {
        $return = new \stdClass();
        $return->server = new ParameterBag([
            'PHP_AUTH_USER' => 'wrong_login',
            'PHP_AUTH_PW' => 'wrong_password',
        ]);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->withAnyParameters()
            ->willReturn($return)
        ;

        $this->fileSystem
            ->expects($this->once())
            ->method('load')
            ->withAnyParameters()
            ->willReturn('login:5fjgIzboD2FrE')
        ;

        $exportAuthenticator = new ExportAuthenticator(
            $this->credentialsCheckerMock,
            $this->requestStack,
            $this->fileSystem
        );

        $this->assertFalse($exportAuthenticator->authenticateByFile('file'));
    }

    public function testAuthenticateByFileWithWrongFile()
    {
        $return = new \stdClass();
        $return->server = new ParameterBag([
            'PHP_AUTH_USER' => 'wrong_login',
            'PHP_AUTH_PW' => 'wrong_password',
        ]);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->withAnyParameters()
            ->willReturn($return)
        ;

        $this->fileSystem
            ->expects($this->never())
            ->method('load')
            ->withAnyParameters()
        ;

        $exportAuthenticator = new ExportAuthenticator(
            $this->credentialsCheckerMock,
            $this->requestStack,
            $this->fileSystem
        );

        $this->assertFalse($exportAuthenticator->authenticateByFile('../file'));
    }

    public function testAuthenticateByFileWithRealFile()
    {
        $return = new \stdClass();
        $return->server = new ParameterBag([
            'PHP_AUTH_USER' => 'login',
            'PHP_AUTH_PW' => 'PassTest00123A',
        ]);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->withAnyParameters()
            ->willReturn($return)
        ;

        $exportAuthenticator = new ExportAuthenticator(
            $this->credentialsCheckerMock,
            $this->requestStack,
            new FileSystemHelper(
                new BaseFilesystem(),
                __DIR__ . '/../fixtures/directory/'
            )
        );

        $this->assertTrue($exportAuthenticator->authenticateByFile('export_directory/the_file'));
    }

    /**
     * @param string $method
     *
     * @return \EzSystems\EzRecommendationClient\Value\Config\ExportCredentials
     */
    private function getExportCredentials(string $method = 'basic'): ExportCredentials
    {
        return new ExportCredentials([
            'method' => $method,
            'login' => '1111',
            'password' => 'password',
        ]);
    }
}
