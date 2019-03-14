<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service;

interface SignalSlotServiceInterface
{
    /**
     * @param int $contentId
     * @param int|null $versionNo
     */
    public function updateContent(int $contentId, ?int $versionNo = null): void;

    /**
     * @param int $contentId
     */
    public function deleteContent(int $contentId): void;

    /**
     * @param int $contentId
     */
    public function hideContent(int $contentId): void;

    /**
     * @param int $contentId
     */
    public function revealContent(int $contentId): void;

    /**
     * @param int $locationId
     * @param bool $isChild
     */
    public function hideLocation(int $locationId, bool $isChild = false): void;

    /**
     * @param int $locationId
     */
    public function unhideLocation(int $locationId): void;

    /**
     * @param int $locationId
     */
    public function swapLocation(int $locationId): void;
}
