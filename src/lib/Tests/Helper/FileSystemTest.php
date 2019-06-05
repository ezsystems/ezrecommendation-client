<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Helper;

use EzSystems\EzRecommendationClient\Helper\FileSystemHelper;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class FileSystemTest extends TestCase
{
    /** @var \Symfony\Component\Filesystem\Filesystem|\PHPUnit\Framework\MockObject\MockObject */
    private $baseFileSystemHelper;

    public function setUp()
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

        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileSystemHelper->load('testfile.txt');

        $this->assertContains('testfile.txt content', $result);
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     * @expectedExceptionMessage File not found.
     */
    public function testLoadUnexistingFile()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('exists')
            ->withAnyParameters()
            ->willReturn(false)
        ;

        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileSystemHelper->load('unexisting_file.txt');
    }

    public function testSave()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('dumpFile')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $FileSystemHelper->save('testfile.txt', 'test');
    }

    public function testGetDir()
    {
        $dir = 'directory/';

        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            $dir
        );

        $result = $FileSystemHelper->getDir();

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

        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileSystemHelper->createChunkDir();

        $this->assertTrue(strlen($result) > 5);
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

        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileSystemHelper->createChunkDir();

        $this->assertTrue(strlen($result) > 5);
    }

    public function testLock()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('touch')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $FileSystemHelper->lock();
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

        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileSystemHelper->unlock();
    }

    public function testUnlockWithoutLockedFile()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('exists')
            ->withAnyParameters()
            ->willReturn(false)
        ;

        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileSystemHelper->unlock();
    }

    public function testiIsLockedWithLockedFile()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('exists')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $this->assertTrue($FileSystemHelper->isLocked());
    }

    public function testiIsLockedWithoutLockedFile()
    {
        $this->baseFileSystemHelper
            ->expects($this->once())
            ->method('exists')
            ->withAnyParameters()
            ->willReturn(false)
        ;

        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $this->assertFalse($FileSystemHelper->isLocked());
    }

    public function testSecureDirWithMethodNone()
    {
        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileSystemHelper->secureDir(
            'dir',
            $this->getExportCredentials('none')
        );

        $this->assertEquals([], $result);
    }

    public function testSecureDirWithMethodUser()
    {
        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileSystemHelper->secureDir(
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

        $FileSystemHelper = new FileSystemHelper(
            $this->baseFileSystemHelper,
            __DIR__ . '/../fixtures/'
        );

        $result = $FileSystemHelper->secureDir(
            'dir',
            $this->getExportCredentials()
        );

        $this->assertEquals('yc', $result['login']);
        $this->assertTrue(strlen($result['password']) > 5);
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
            'login' => 0001,
            'password' => 'pass',
        ]);
    }
}
