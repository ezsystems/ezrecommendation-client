<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\File;

use EzSystems\EzRecommendationClient\Exception\FileNotFoundException;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use EzSystems\EzRecommendationClient\Value\ExportMethod;
use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;

final class FileManager implements FileManagerInterface
{
    /** @var \Symfony\Component\Filesystem\Filesystem */
    private $filesystem;

    /** @var string */
    private $exportDocumentRoot;

    public function __construct(
        BaseFilesystem $filesystem,
        string $exportDocumentRoot
    ) {
        $this->filesystem = $filesystem;
        $this->exportDocumentRoot = $exportDocumentRoot;
    }

    /**
     * @throws \EzSystems\EzRecommendationClient\Exception\FileNotFoundException
     */
    public function load(string $file): ?string
    {
        $dir = $this->getDir();

        if (!$this->filesystem->exists($dir . $file)) {
            throw new FileNotFoundException(sprintf('File: %s not found.', $file));
        }

        return file_get_contents($dir . $file);
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $file, string $content): void
    {
        $this->filesystem->dumpFile($file, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function getDir(): string
    {
        return $this->exportDocumentRoot;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function lock(): void
    {
        $dir = $this->getDir();

        $this->filesystem->touch($dir . '.lock');
    }

    /**
     * {@inheritdoc}
     */
    public function unlock(): void
    {
        $dir = $this->getDir();

        if ($this->filesystem->exists($dir . '.lock')) {
            $this->filesystem->remove($dir . '.lock');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isLocked(): bool
    {
        $dir = $this->getDir();

        return $this->filesystem->exists($dir . '.lock');
    }

    /**
     * {@inheritdoc}
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
