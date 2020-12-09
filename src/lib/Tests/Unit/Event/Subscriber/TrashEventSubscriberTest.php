<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Unit\Event\Subscriber;

use eZ\Publish\API\Repository\Events\Trash\RecoverEvent;
use eZ\Publish\API\Repository\Events\Trash\TrashEvent;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationList;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\Relation;
use EzSystems\EzRecommendationClient\Event\Subscriber\TrashEventSubscriber;
use EzSystems\EzRecommendationClient\Tests\Common\Event\Subscriber\AbstractRepositoryEventSubscriberTest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TrashEventSubscriberTest extends AbstractRepositoryEventSubscriberTest
{
    private const CONTENT_ID = 123;

    /** @var \EzSystems\EzRecommendationClient\Event\Subscriber\TrashEventSubscriber */
    private $trashEventSubscriber;

    /** @var \eZ\Publish\API\Repository\Repository|\PHPUnit\Framework\MockObject\MockObject */
    private $repositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock = $this->createMock(Repository::class);
        $this->trashEventSubscriber = new TrashEventSubscriber(
            $this->notificationServiceMock,
            $this->contentServiceMock,
            $this->locationServiceMock,
            $this->locationHelperMock,
            $this->contentHelperMock,
            $this->repositoryMock
        );
    }

    public function testCreateInstanceOfTrashEventSubscriber()
    {
        $this->assertInstanceOf(TrashEventSubscriber::class, $this->trashEventSubscriber);
    }

    public function getEventSubscriber(): EventSubscriberInterface
    {
        return $this->trashEventSubscriber;
    }

    public function subscribedEventsDataProvider(): array
    {
        return [
            [RecoverEvent::class],
            [TrashEvent::class],
        ];
    }

    public function testCallOnTrashMethod()
    {
        $event = $this->createMock(TrashEvent::class);
        $event
            ->expects($this->atLeastOnce())
            ->method('getLocation')
            ->willReturn($this->location);

        $contentInfo = $this->contentInfo;
        $this->repositoryMock
            ->method('sudo')
            ->with(function () use ($contentInfo) {})
            ->willReturn($this->getRelationList());

        $this->contentServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadContentInfo')
            ->willReturn($this->contentInfo);

        $this->trashEventSubscriber->onTrash($event);
    }

    public function testCallOnRecoverMethod()
    {
        $event = $this->createMock(RecoverEvent::class);
        $event
            ->expects($this->atLeastOnce())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->locationServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadLocationChildren')
            ->with($this->location)
            ->willReturn($this->getLocationChildren());

        $this->contentServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadContentInfo')
            ->willReturn($this->contentInfo);

        $contentInfo = $this->contentInfo;
        $this->repositoryMock
            ->method('sudo')
            ->with(function () use ($contentInfo) {})
            ->willReturn($this->getRelationList());

        $this->contentServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadContentInfo')
            ->willReturn($this->contentInfo);

        $this->trashEventSubscriber->onRecover($event);
    }

    private function getRelationList()
    {
        return array_map(function (ContentInfo $contentInfo) {
            return new Relation([
                'destinationContentInfo' => $contentInfo,
            ]);
        }, $this->getReverseRelations());
    }

    private function getReverseRelations(): array
    {
        return [
            new ContentInfo([
                'id' => 1,
                'contentTypeId' => 2,
            ]),
            new ContentInfo([
                'id' => 2,
                'contentTypeId' => 2,
            ]),
            new ContentInfo([
                'id' => 3,
                'contentTypeId' => 3,
            ]),
        ];
    }

    private function getLocationChildren(): LocationList
    {
        return new LocationList([
            'totalCount' => 2,
            'locations' => [
                new Location([
                    'id' => 20,
                    'path' => ['1', '5', '20'],
                    'contentInfo' => new ContentInfo(['id' => self::CONTENT_ID + 1]),
                ]),
                new Location([
                    'id' => 30,
                    'path' => ['1', '5', '30'],
                    'contentInfo' => new ContentInfo(['id' => self::CONTENT_ID + 2]),
                ]),
            ],
        ]);
    }
}
