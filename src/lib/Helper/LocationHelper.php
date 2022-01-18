<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

final class LocationHelper
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    public function __construct(
        LocationServiceInterface $locationService,
        ContentServiceInterface $contentService
    ) {
        $this->locationService = $locationService;
        $this->contentService = $contentService;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function areLocationsVisible(ContentInfo $contentInfo): bool
    {
        $contentLocations = $this->locationService->loadLocations($contentInfo);

        foreach ($contentLocations as $contentLocation) {
            if (!$contentLocation->hidden) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns location path string based on $contentId.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getParentLocationPathString(int $contentId): ?string
    {
        try {
            $content = $this->contentService->loadContent($contentId);
            $mainLocation = $content->contentInfo->getMainLocation();
            if (null === $mainLocation) {
                return null;
            }

            $parentLocation = $mainLocation->getParentLocation();

            return null !== $parentLocation ? $parentLocation->pathString : null;
        } catch (NotFoundException $exception) {
            return null;
        }
    }
}
