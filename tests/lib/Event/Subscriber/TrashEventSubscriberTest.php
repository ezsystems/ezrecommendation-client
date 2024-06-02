<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Event\Subscriber;

use eZ\Publish\API\Repository\Events\Trash\RecoverEvent;
use eZ\Publish\API\Repository\Events\Trash\TrashEvent;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\Relation;
use EzSystems\EzRecommendationClient\Event\Subscriber\TrashEventSubscriber;
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
            $this->queryFactoryMock,
            $this->searchServiceMock,
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
        $relationContentInfo1 = new ContentInfo(['id' => 1, 'contentTypeId' => 2]);
        $relationContentInfo2 = new ContentInfo(['id' => 2, 'contentTypeId' => 2]);
        $relationContentInfo3 = new ContentInfo(['id' => 3, 'contentTypeId' => 3]);

        $event = $this->createMock(TrashEvent::class);
        $event
            ->expects($this->atLeastOnce())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->repositoryMock
            ->method('sudo')
            ->willReturn($this->getRelationList());

        $this->contentServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadContentInfo')
            ->willReturnOnConsecutiveCalls(
                $relationContentInfo1,
                $relationContentInfo2,
                $relationContentInfo3,
            );

        $this->notificationServiceMock
            ->expects($this->exactly(4))
            ->method('sendNotification')
            ->withConsecutive(
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\TrashEventSubscriber::onTrash',
                    'DELETE',
                    $this->contentInfo,
                ],
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\TrashEventSubscriber::updateRelations',
                    'UPDATE',
                    $relationContentInfo1,
                ],
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\TrashEventSubscriber::updateRelations',
                    'UPDATE',
                    $relationContentInfo2,
                ],
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\TrashEventSubscriber::updateRelations',
                    'UPDATE',
                    $relationContentInfo3,
                ],
            );

        $this->trashEventSubscriber->onTrash($event);
    }

    public function testCallOnRecoverMethod()
    {
        $relationContentInfo1 = new ContentInfo(['id' => 1, 'contentTypeId' => 2]);
        $relationContentInfo2 = new ContentInfo(['id' => 2, 'contentTypeId' => 2]);
        $relationContentInfo3 = new ContentInfo(['id' => 3, 'contentTypeId' => 3]);

        $locationContentInfo1 = new ContentInfo(['id' => self::CONTENT_ID + 1]);
        $locationContentInfo2 = new ContentInfo(['id' => self::CONTENT_ID + 2]);

        $event = $this->createMock(RecoverEvent::class);
        $event
            ->expects($this->atLeastOnce())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->queryFactoryMock
            ->method('create')
            ->willReturn(new LocationQuery());

        $this->searchServiceMock
            ->method('findLocations')
            ->willReturn($this->getLocationChildrenSearchResult());

        $this->contentServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadContentInfo')
            ->willReturnOnConsecutiveCalls(
                $relationContentInfo1,
                $relationContentInfo2,
                $relationContentInfo3,
            );

        $this->repositoryMock
            ->method('sudo')
            ->willReturn($this->getRelationList());

        $this->notificationServiceMock
            ->expects($this->exactly(5))
            ->method('sendNotification')
            ->withConsecutive(
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\TrashEventSubscriber::onRecover',
                    'UPDATE',
                    $locationContentInfo1,
                ],
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\TrashEventSubscriber::onRecover',
                    'UPDATE',
                    $locationContentInfo2,
                ],
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\TrashEventSubscriber::updateRelations',
                    'UPDATE',
                    $relationContentInfo1,
                ],
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\TrashEventSubscriber::updateRelations',
                    'UPDATE',
                    $relationContentInfo2,
                ],
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\TrashEventSubscriber::updateRelations',
                    'UPDATE',
                    $relationContentInfo3,
                ],
            );

        $this->trashEventSubscriber->onRecover($event);
    }

    private function getRelationList()
    {
        return array_map(static function (ContentInfo $contentInfo) {
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

    private function getLocationChildrenSearchResult(): SearchResult
    {
        return new SearchResult([
            'searchHits' => [
                new SearchHit(['valueObject' => new Location([
                    'id' => 20,
                    'path' => ['1', '5', '20'],
                    'contentInfo' => new ContentInfo(['id' => self::CONTENT_ID + 1]),
                ])]),
                new SearchHit(['valueObject' => new Location([
                    'id' => 30,
                    'path' => ['1', '5', '30'],
                    'contentInfo' => new ContentInfo(['id' => self::CONTENT_ID + 2]),
                ])]),
            ],
        ]);
    }
}
