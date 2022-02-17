<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

final class LocationHelper
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    public function __construct(LocationServiceInterface $locationService)
    {
        $this->locationService = $locationService;
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
}
