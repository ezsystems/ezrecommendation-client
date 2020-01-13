<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use EzSystems\EzRecommendationClient\Helper\ContentHelper;
use EzSystems\EzRecommendationClient\Helper\LocationHelper;
use EzSystems\EzRecommendationClient\Service\NotificationService;

abstract class AbstractRepositoryEventSubscriber extends AbstractCoreEventSubscriber
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    protected $contentService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    protected $locationService;

    /** @var \EzSystems\EzRecommendationClient\Helper\LocationHelper */
    protected $locationHelper;

    /** @var \EzSystems\EzRecommendationClient\Helper\ContentHelper */
    protected $contentHelper;

    public function __construct(
        NotificationService $notificationService,
        ContentServiceInterface $contentService,
        LocationServiceInterface $locationService,
        LocationHelper $locationHelper,
        ContentHelper $contentHelper
    ) {
        parent::__construct($notificationService);
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->locationHelper = $locationHelper;
        $this->contentHelper = $contentHelper;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function updateLocationSubtree(Location $location, string $method, string $action): void
    {
        $subtree = $this->locationService->loadLocationChildren($location);

        /** @var \eZ\Publish\API\Repository\Values\Content\Location $content */
        foreach ($subtree as $content) {
            $this->notificationService->sendNotification(
                $method, $action, $content->getContentInfo()
            );
        }
    }
}
