<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Query\QueryFactoryInterface;
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

    /** @var \eZ\Publish\Core\Query\QueryFactoryInterface */
    private $queryFactory;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    public function __construct(
        NotificationService $notificationService,
        ContentServiceInterface $contentService,
        LocationServiceInterface $locationService,
        LocationHelper $locationHelper,
        ContentHelper $contentHelper,
        QueryFactoryInterface $queryFactory,
        SearchService $searchService
    ) {
        parent::__construct($notificationService);
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->locationHelper = $locationHelper;
        $this->contentHelper = $contentHelper;
        $this->queryFactory = $queryFactory;
        $this->searchService = $searchService;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function updateLocationSubtree(Location $location, string $method, string $action): void
    {
        $locationChildren = $this->loadLocationChildren($location);

        foreach ($locationChildren as $locationChild) {
            $this->notificationService->sendNotification(
                $method, $action, $locationChild->getContentInfo()
            );
        }
    }

    /**
     * @return array<\eZ\Publish\API\Repository\Values\Content\Location>
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function loadLocationChildren(Location $location): array
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\LocationQuery $locationChildrenQuery */
        $locationChildrenQuery = $this->queryFactory->create('Children', ['location' => $location]);
        $searchResult = $this->searchService->findLocations($locationChildrenQuery);

        $locations = [];
        foreach ($searchResult->searchHits as $searchHit) {
            /** @var \eZ\Publish\API\Repository\Values\Content\Location $locationChild */
            $locationChild = $searchHit->valueObject;
            $locations[] = $locationChild;
        }

        return $locations;
    }
}
