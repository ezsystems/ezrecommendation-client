<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Events\Trash\RecoverEvent;
use eZ\Publish\API\Repository\Events\Trash\TrashEvent;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use EzSystems\EzRecommendationClient\Helper\ContentHelper;
use EzSystems\EzRecommendationClient\Helper\LocationHelper;
use EzSystems\EzRecommendationClient\Service\NotificationService;
use EzSystems\EzRecommendationClient\Value\EventNotification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TrashEventSubscriber extends AbstractRepositoryEventSubscriber implements EventSubscriberInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    public function __construct(
        NotificationService $notificationService,
        ContentServiceInterface $contentService,
        LocationServiceInterface $locationService,
        LocationHelper $locationHelper,
        ContentHelper $contentHelper,
        Repository $repository
    ) {
        parent::__construct($notificationService, $contentService, $locationService, $locationHelper, $contentHelper);

        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RecoverEvent::class => ['onRecover', parent::EVENT_PRIORITY],
            TrashEvent::class => ['onTrash', parent::EVENT_PRIORITY],
        ];
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onRecover(RecoverEvent $event): void
    {
        $this->updateLocationSubtree(
            $event->getLocation(),
            __METHOD__,
            EventNotification::ACTION_UPDATE
        );

        $this->updateRelations(
            $this->getRelations($event->getLocation()->getContentInfo())
        );
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onTrash(TrashEvent $event): void
    {
        $this->notificationService->sendNotification(
            __METHOD__,
            EventNotification::ACTION_DELETE,
            $event->getLocation()->getContentInfo()
        );

        $this->updateRelations(
            $this->getRelations($event->getLocation()->getContentInfo())
        );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getRelations(ContentInfo $contentInfo): array
    {
        /** Sudo must be used to have access to trash and load content relations, since Client using this EventSubscriber operates as a User without privileges. */
        return $this->repository->sudo(function () use ($contentInfo) {
            return $this->contentService->loadReverseRelations($contentInfo);
        });
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Relation[] $relations
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function updateRelations(array $relations): void
    {
        foreach ($relations as $relation) {
            $this->notificationService->sendNotification(
                __METHOD__,
                EventNotification::ACTION_UPDATE,
                $this->contentService->loadContentInfo($relation->destinationContentInfo->id)
            );
        }
    }
}
