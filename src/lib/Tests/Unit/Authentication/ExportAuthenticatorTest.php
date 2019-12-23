<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Unit\Authentication;

use EzSystems\EzRecommendationClient\Authentication\ExportAuthenticator;
use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use EzSystems\EzRecommendationClient\File\FileManager;
use EzSystems\EzRecommendationClient\File\FileManagerInterface;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;

class ExportAuthenticatorTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\EzSystems\EzRecommendationClient\File\FileManagerInterface */
    private $fileManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface */
    private $credentialsResolverMock;

    public function setUp() : void
    {
        parent::setUp();

        $this->credentialsResolverMock = $this->getMockBuilder(CredentialsResolverInterface::class)->getMock();
        $this->requestStack = $this->getMockBuilder(RequestStack::class)->getMock();
        $this->fileManager = $this->getMockBuilder(FileManagerInterface::class)->disableOriginalConstructor()->getMock();
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
            $this->credentialsResolverMock,
            $this->requestStack,
            $this->fileManager
        );

        $this->credentialsResolverMock
            ->expects($this->atLeastOnce())
            ->method('getCredentials')
            ->willReturn($this->getExportCredentials('none'));

        $this->assertTrue($exportAuthenticator->authenticate());
    }

    public function testAuthenticateWithMethodUser()
    {
        $return = new \stdClass();
        $return->server = new ParameterBag([
            'PHP_AUTH_USER' => 1111,
            'PHP_AUTH_PW' => 'password',
        ]);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->withAnyParameters()
            ->willReturn($return)
        ;

        $exportAuthenticator = new ExportAuthenticator(
            $this->credentialsResolverMock,
            $this->requestStack,
            $this->fileManager
        );

        $this->credentialsResolverMock
            ->expects($this->atLeastOnce())
            ->method('getCredentials')
            ->willReturn($this->getExportCredentials('user'));

        $this->assertTrue($exportAuthenticator->authenticate());
    }

    public function testAuthenticateWithMethodUserAndWrongCredentials()
    {
        $return = new \stdClass();
        $return->server = new ParameterBag([
            'PHP_AUTH_USER' => 12345,
            'PHP_AUTH_PW' => 'wrong_password',
        ]);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->withAnyParameters()
            ->willReturn($return)
        ;

        $exportAuthenticator = new ExportAuthenticator(
            $this->credentialsResolverMock,
            $this->requestStack,
            $this->fileManager
        );

        $this->credentialsResolverMock
            ->expects($this->atLeastOnce())
            ->method('getCredentials')
            ->willReturn($this->getExportCredentials('user'));

        $this->assertFalse($exportAuthenticator->authenticate());
    }

    /**
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
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

        $this->fileManager
            ->expects($this->once())
            ->method('load')
            ->withAnyParameters()
            ->willReturn('login:5fjgIzboD2FrE')
        ;

        $exportAuthenticator = new ExportAuthenticator(
            $this->credentialsResolverMock,
            $this->requestStack,
            $this->fileManager
        );

        $this->assertTrue($exportAuthenticator->authenticateByFile('file'));
    }

    /**
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
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

        $this->fileManager
            ->expects($this->once())
            ->method('load')
            ->withAnyParameters()
            ->willReturn('login:5fjgIzboD2FrE')
        ;

        $exportAuthenticator = new ExportAuthenticator(
            $this->credentialsResolverMock,
            $this->requestStack,
            $this->fileManager
        );

        $this->assertFalse($exportAuthenticator->authenticateByFile('file'));
    }

    /**
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
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

        $this->fileManager
            ->expects($this->never())
            ->method('load')
            ->withAnyParameters()
        ;

        $exportAuthenticator = new ExportAuthenticator(
            $this->credentialsResolverMock,
            $this->requestStack,
            $this->fileManager
        );

        $this->assertFalse($exportAuthenticator->authenticateByFile('../file'));
    }

    /**
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
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
            $this->credentialsResolverMock,
            $this->requestStack,
            new FileManager(
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
            'login' => 1111,
            'password' => 'password',
        ]);
    }
}
