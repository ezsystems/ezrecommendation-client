<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use eZ\Publish\Core\REST\Common\Exceptions\NotFoundException;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use EzSystems\EzRecommendationClient\Value\ExportMethod;
use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;

/**
 * Provides utility to manipulate the file system for export purposes.
 */
class FileSystemHelper
{
    /** @var \Symfony\Component\Filesystem\Filesystem */
    private $filesystem;

    /** @var string */
    private $exportDocumentRoot;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param string $exportDocumentRoot
     */
    public function __construct(
        BaseFilesystem $filesystem,
        string $exportDocumentRoot
    ) {
        $this->filesystem = $filesystem;
        $this->exportDocumentRoot = $exportDocumentRoot;
    }

    /**
     * Load the content from file.
     *
     * @param string $file
     *
     * @return string
     *
     * @throws NotFoundException when file not found.
     */
    public function load(string $file): string
    {
        $dir = $this->getDir();

        if (!$this->filesystem->exists($dir . $file)) {
            throw new NotFoundException('File not found.');
        }

        return file_get_contents($dir . $file);
    }

    /**
     * Saves the content to file.
     *
     * @param string $file
     * @param string $content
     */
    public function save(string $file, string $content): void
    {
        $this->filesystem->dumpFile($file, $content);
    }

    /**
     * Returns directory for export files or default directory if not exists.
     *
     * @return string
     */
    public function getDir(): string
    {
        return $this->exportDocumentRoot;
    }

    /**
     * Generates directory for export files.
     *
     * @return string
     */
    public function createChunkDir(): string
    {
        $directoryName = date('Y/m/d/H/i/', time());
        $dir = $this->getDir() . $directoryName;

        if (!$this->filesystem->exists($dir)) {
            $this->filesystem->mkdir($dir, 0755);
        }

        return $directoryName;
    }

    /**
     * Locks directory by creating lock file.
     */
    public function lock(): void
    {
        $dir = $this->getDir();

        $this->filesystem->touch($dir . '.lock');
    }

    /**
     * Unlock directory by deleting lock file.
     */
    public function unlock(): void
    {
        $dir = $this->getDir();

        if ($this->filesystem->exists($dir . '.lock')) {
            $this->filesystem->remove($dir . '.lock');
        }
    }

    /**
     * Checks if directory is locked.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        $dir = $this->getDir();

        return $this->filesystem->exists($dir . '.lock');
    }

    /**
     * Securing the directory regarding the authentication method.
     *
     * @param string $chunkDir
     * @param array $credentials
     *
     * @return array
     */
    public function secureDir(string $chunkDir, ExportCredentials $credentials): array
    {
        $dir = $this->getDir() . $chunkDir;

        if ($credentials->getMethod() === ExportMethod::NONE) {
            return [];
        } elseif ($credentials->getMethod() === ExportMethod::USER) {
            return [
                'login' => $credentials->getLogin(),
                'password' => $credentials->getPassword(),
            ];
        }

        $user = 'yc';
        $password = substr(md5(microtime()), 0, 10);

        $this->filesystem->dumpFile(
            $dir . '.htpasswd',
            sprintf('%s:%s', $user, crypt($password, md5($password)))
        );

        return [
            'login' => $user,
            'password' => $password,
        ];
    }
}
