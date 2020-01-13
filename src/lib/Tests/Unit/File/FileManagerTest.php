<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Unit\File;

use EzSystems\EzRecommendationClient\Exception\FileNotFoundException;
use EzSystems\EzRecommendationClient\File\FileManager;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class FileManagerTest extends TestCase
{
    /** @var \Symfony\Component\Filesystem\Filesystem|\PHPUnit\Framework\MockObject\MockObject */
    private $baseFileSystemHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->baseFileSystemHelper = $this->getMockBuilder(Filesystem::class)->getMock();
    }

    public function testLoad()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('exists')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileManager->load('testfile.txt');

        $this->assertStringContainsString('testfile.txt content', $result);
    }

    public function testLoadUnexistingFile()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('exists')
            ->withAnyParameters()
            ->willReturn(false)
        ;

        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File: unexisting_file.txt not found.');
        $FileManager->load('unexisting_file.txt');
    }

    public function testSave()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('dumpFile')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $FileManager->save('testfile.txt', 'test');
    }

    public function testGetDir()
    {
        $dir = 'directory/';

        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            $dir
        );

        $result = $FileManager->getDir();

        $this->assertEquals($dir, $result);
    }

    public function testCreateChunkDir()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('exists')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileManager->createChunkDir();

        $this->assertTrue(\strlen($result) > 5);
    }

    public function testCreateChunkDirWithUnexistingDir()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('exists')
            ->withAnyParameters()
            ->willReturn(false)
        ;

        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('mkdir')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileManager->createChunkDir();

        $this->assertTrue(\strlen($result) > 5);
    }

    public function testLock()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('touch')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $FileManager->lock();
    }

    public function testUnlock()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('exists')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('remove')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $FileManager->unlock();
    }

    public function testUnlockWithoutLockedFile()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('exists')
            ->withAnyParameters()
            ->willReturn(false)
        ;

        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $FileManager->unlock();
    }

    public function testIsLockedWithLockedFile()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('exists')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $this->assertTrue($FileManager->isLocked());
    }

    public function testIsLockedWithoutLockedFile()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('exists')
            ->withAnyParameters()
            ->willReturn(false)
        ;

        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $this->assertFalse($FileManager->isLocked());
    }

    public function testSecureDirWithMethodNone()
    {
        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileManager->secureDir(
            'dir',
            $this->getExportCredentials('none')
        );

        $this->assertEquals([], $result);
    }

    public function testSecureDirWithMethodUser()
    {
        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileManager->secureDir(
            'dir',
            $this->getExportCredentials('user')
        );

        $this->assertEquals(
            [
                'login' => 0001,
                'password' => 'pass',
            ],
            $result
        );
    }

    public function testSecureDirWithMethodBasic()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('dumpFile')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $FileManager = new FileManager(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileManager->secureDir(
            'dir',
            $this->getExportCredentials()
        );

        $this->assertEquals('yc', $result['login']);
        $this->assertTrue(\strlen($result['password']) > 5);
    }

    private function getExportCredentials(string $method = 'basic'): ExportCredentials
    {
        return new ExportCredentials([
            'method' => $method,
            'login' => 0001,
            'password' => 'pass',
        ]);
    }
}
