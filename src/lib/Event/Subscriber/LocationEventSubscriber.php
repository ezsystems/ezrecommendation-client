<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use eZ\Publish\API\Repository\Events\Location\CopySubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\CreateLocationEvent;
use eZ\Publish\API\Repository\Events\Location\DeleteLocationEvent;
use eZ\Publish\API\Repository\Events\Location\HideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\MoveSubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\SwapLocationEvent;
use eZ\Publish\API\Repository\Events\Location\UnhideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\UpdateLocationEvent;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use EzSystems\EzRecommendationClient\Value\EventNotification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LocationEventSubscriber extends AbstractRepositoryEventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CopySubtreeEvent::class => ['onCopySubtree', parent::EVENT_PRIORITY],
            CreateLocationEvent::class => ['onCreateLocation', parent::EVENT_PRIORITY],
            DeleteLocationEvent::class => ['onDeleteLocation', parent::EVENT_PRIORITY],
            HideLocationEvent::class => ['onHideLocation', parent::EVENT_PRIORITY],
            MoveSubtreeEvent::class => ['onMoveSubtree', parent::EVENT_PRIORITY],
            SwapLocationEvent::class => ['onSwapLocation', parent::EVENT_PRIORITY],
            UnhideLocationEvent::class => ['onUnhideLocation', parent::EVENT_PRIORITY],
            UpdateLocationEvent::class => ['onUpdateLocation', parent::EVENT_PRIORITY],
        ];
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onCopySubtree(CopySubtreeEvent $event): void
    {
        $this->updateLocationSubtree(
            $event->getLocation(),
            __METHOD__,
            EventNotification::ACTION_UPDATE
        );
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onCreateLocation(CreateLocationEvent $event): void
    {
        $this->notificationService->sendNotification(
            __METHOD__,
            EventNotification::ACTION_UPDATE,
            $event->getContentInfo()
        );
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onDeleteLocation(DeleteLocationEvent $event): void
    {
        $this->updateLocationWithChildren(
            $event->getLocation(),
            __METHOD__,
            EventNotification::ACTION_DELETE,
        );
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onHideLocation(HideLocationEvent $event): void
    {
        $this->hideLocation($event->getLocation());
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onMoveSubtree(MoveSubtreeEvent $event): void
    {
        $this->updateLocationSubtree(
            $event->getLocation(),
            __METHOD__,
            EventNotification::ACTION_UPDATE
        );
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onSwapLocation(SwapLocationEvent $event): void
    {
        $this->swapLocation([
            $event->getLocation1(),
            $event->getLocation2(),
        ]);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onUnhideLocation(UnhideLocationEvent $event): void
    {
        $this->updateLocationWithChildren(
            $event->getLocation(),
            __METHOD__,
            EventNotification::ACTION_UPDATE
        );
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onUpdateLocation(UpdateLocationEvent $event): void
    {
        $this->notificationService->sendNotification(
            __METHOD__,
            EventNotification::ACTION_UPDATE,
            $event->getLocation()->getContentInfo()
        );
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function hideLocation(Location $location, bool $isChild = false): void
    {
        $children = $this->locationService->loadLocationChildren($location)->locations;

        /** @var \eZ\Publish\API\Repository\Values\Content\Location $child */
        foreach ($children as $child) {
            $this->hideLocation($child, true);
        }

        $content = $this->contentHelper->getIncludedContent(
            $this->locationService->loadLocation($location->id)->contentId
        );

        if (!$content instanceof Content) {
            return;
        }

        if (!$isChild && $this->locationHelper->areLocationsVisible($content->contentInfo)) {
            return;
        }

        $this->notificationService->sendNotification(
            __METHOD__, EventNotification::ACTION_DELETE, $content->contentInfo
        );
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function updateLocationWithChildren(Location $location, string $method, string $action): void
    {
        $children = $this->locationService->loadLocationChildren($location)->locations;

        /** @var \eZ\Publish\API\Repository\Values\Content\Location $child */
        foreach ($children as $child) {
            $this->updateLocationWithChildren($child, $method, $action);
        }

        $content = $this->contentHelper->getIncludedContent(
            $this->locationService->loadLocation($location->id)->contentId
        );

        if (!$content instanceof Content) {
            return;
        }

        $this->notificationService->sendNotification(
            $method, $action, $content->contentInfo
        );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Location[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function swapLocation(array $locations): void
    {
        foreach ($locations as $location) {
            $this->updateLocationWithChildren(
                $location,
                __METHOD__,
                EventNotification::ACTION_UPDATE
            );
        }
    }
}
