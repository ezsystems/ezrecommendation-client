<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\File;

use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;

interface FileManagerInterface
{
    /**
     * Loads file.
     *
     * @param string $file
     *
     * @return string
     */
    public function load(string $file): ?string;

    /**
     * Saves the content to file.
     *
     * @param string $file
     * @param string $content
     */
    public function save(string $file, string $content): void;

    /**
     * Returns directory.
     *
     * @return string
     */
    public function getDir(): string;

    /**
     * Generates directory.
     *
     * @return string
     */
    public function createChunkDir(): string;

    /**
     * Locks directory by creating lock file.
     */
    public function lock(): void;

    /**
     * Unlock directory by deleting lock file.
     */
    public function unlock(): void;

    /**
     * Checks if directory is locked.
     *
     * @return bool
     */
    public function isLocked(): bool;

    /**
     * Securing the directory regarding the authentication method.
     *
     * @param string $chunkDir
     * @param \EzSystems\EzRecommendationClient\Value\Config\ExportCredentials $credentials
     *
     * @return array
     */
    public function secureDir(string $chunkDir, ExportCredentials $credentials): array;
}
