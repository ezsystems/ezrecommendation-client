<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Event\Subscriber;

use eZ\Publish\API\Repository\Events\Location\CopySubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\CreateLocationEvent;
use eZ\Publish\API\Repository\Events\Location\HideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\MoveSubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\SwapLocationEvent;
use eZ\Publish\API\Repository\Events\Location\UnhideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\UpdateLocationEvent;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocationEventSubscriberTest extends AbstractRepositoryEventSubscriberTest
{
    private const CONTENT_ID = 1;

    private const CONTENT_TYPE_ID = 2;

    /** @var \EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber */
    private $locationEventSubscriber;

    /** @var \eZ\Publish\Core\Repository\Values\Content\Location */
    private $location1;

    /** @var \eZ\Publish\Core\Repository\Values\Content\Location */
    private $location2;

    public function setUp(): void
    {
        parent::setUp();

        $this->locationEventSubscriber = new LocationEventSubscriber(
            $this->notificationServiceMock,
            $this->contentServiceMock,
            $this->locationServiceMock,
            $this->locationHelperMock,
            $this->contentHelperMock,
            $this->queryFactoryMock,
            $this->searchServiceMock
        );
        $this->location1 = new Location([
            'id' => 20,
            'path' => ['1', '5', '20'],
            'contentInfo' => new ContentInfo(['id' => self::CONTENT_ID + 1]),
        ]);
        $this->location2 = new Location([
            'id' => 30,
            'path' => ['1', '5', '30'],
            'contentInfo' => new ContentInfo(['id' => self::CONTENT_ID + 2]),
        ]);
    }

    public function testCreateInstanceOfLocationEventSubscriber()
    {
        $this->assertInstanceOf(LocationEventSubscriber::class, $this->locationEventSubscriber);
    }

    public function getEventSubscriber(): EventSubscriberInterface
    {
        return $this->locationEventSubscriber;
    }

    public function subscribedEventsDataProvider(): array
    {
        return [
            [CopySubtreeEvent::class],
            [CreateLocationEvent::class],
            [HideLocationEvent::class],
            [MoveSubtreeEvent::class],
            [SwapLocationEvent::class],
            [UnhideLocationEvent::class],
            [UpdateLocationEvent::class],
        ];
    }

    public function testCallOnCopySubtreeMethod()
    {
        $event = $this->createMock(CopySubtreeEvent::class);
        $event
            ->expects(self::once())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->queryFactoryMock
            ->method('create')
            ->willReturn(new LocationQuery());

        $this->searchServiceMock
            ->method('findLocations')
            ->willReturn(new SearchResult([
                'searchHits' => [
                    new SearchHit(['valueObject' => $this->location1]),
                    new SearchHit(['valueObject' => $this->location2]),
                ],
            ]));

        $this->notificationServiceMock
            ->expects(self::exactly(2))
            ->method('sendNotification')
            ->withConsecutive(
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber::onCopySubtree',
                    'UPDATE',
                    $this->location1->getContentInfo(),
                ],
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber::onCopySubtree',
                    'UPDATE',
                    $this->location2->getContentInfo(),
                ],
            );

        $this->locationEventSubscriber->onCopySubtree($event);
    }

    public function testCallOnCreateLocationMethod()
    {
        $event = $this->createMock(CreateLocationEvent::class);
        $event
            ->expects(self::once())
            ->method('getContentInfo')
            ->willReturn($this->contentInfo);

        $this->notificationServiceMock
            ->expects(self::once())
            ->method('sendNotification')
            ->with(
                'EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber::onCreateLocation',
                'UPDATE',
                $this->contentInfo
            );

        $this->locationEventSubscriber->onCreateLocation($event);
    }

    public function testCallOnHideLocationMethod()
    {
        $event = $this->createMock(HideLocationEvent::class);
        $event
            ->method('getLocation')
            ->willReturn($this->location);

        $this->locationServiceMock
            ->method('loadLocation')
            ->wilLReturn($this->location);

        $this->contentHelperMock
            ->method('getIncludedContent')
            ->willReturn($this->content);

        $this->queryFactoryMock
            ->method('create')
            ->willReturn(new LocationQuery());

        $this->searchServiceMock
            ->method('findLocations')
            ->willReturn(new SearchResult([]));

        $this->notificationServiceMock
            ->expects(self::once())
            ->method('sendNotification')
            ->withConsecutive(
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber::hideLocation',
                    'DELETE',
                    $this->location->getContentInfo(),
                ],
            );

        $this->locationEventSubscriber->onHideLocation($event);
    }

    public function testCallOnMoveSubtreeMethod()
    {
        $event = $this->createMock(MoveSubtreeEvent::class);
        $event
            ->expects(self::once())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->queryFactoryMock
            ->method('create')
            ->willReturn(new LocationQuery());

        $this->searchServiceMock
            ->method('findLocations')
            ->willReturn(new SearchResult([
                'searchHits' => [
                    new SearchHit(['valueObject' => $this->location1]),
                    new SearchHit(['valueObject' => $this->location2]),
                ],
            ]));

        $this->notificationServiceMock
            ->expects(self::exactly(2))
            ->method('sendNotification')
            ->withConsecutive(
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber::onMoveSubtree',
                    'UPDATE',
                    $this->location1->getContentInfo(),
                ],
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber::onMoveSubtree',
                    'UPDATE',
                    $this->location2->getContentInfo(),
                ],
            );

        $this->locationEventSubscriber->onMoveSubtree($event);
    }

    public function testCallOnSwapLocationMethod()
    {
        $swappedLocationContentInfo = new ContentInfo(['id' => self::CONTENT_ID + 120]);
        $swappedLocation = new Location([
            'id' => 120,
            'path' => ['1', '5', '120'],
            'contentInfo' => $swappedLocationContentInfo,
        ]);
        $swappedLocationContent = new Content([
            'versionInfo' => new VersionInfo([
                'contentInfo' => new ContentInfo([
                    'id' => self::CONTENT_ID + 120,
                ]),
            ]),
            'internalFields' => [],
        ]);

        $event = $this->createMock(SwapLocationEvent::class);
        $event
            ->expects(self::once())
            ->method('getLocation1')
            ->willReturn($this->location);
        $event
            ->expects(self::once())
            ->method('getLocation2')
            ->willReturn($swappedLocation);

        $this->queryFactoryMock
            ->method('create')
            ->willReturn(new LocationQuery());

        $this->searchServiceMock
            ->method('findLocations')
            ->willReturn(new SearchResult([]));

        $this->locationServiceMock
            ->expects(self::at(0))
            ->method('loadLocation')
            ->willReturn($this->location);

        $this->locationServiceMock
            ->expects(self::at(1))
            ->method('loadLocation')
            ->willReturn($swappedLocation);

        $this->contentHelperMock
            ->method('getIncludedContent')
            ->willReturnOnConsecutiveCalls($this->content, $swappedLocationContent);

        $this->notificationServiceMock
            ->expects(self::exactly(2))
            ->method('sendNotification')
            ->withConsecutive(
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber::swapLocation',
                    'UPDATE',
                    $this->location->getContentInfo(),
                ],
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber::swapLocation',
                    'UPDATE',
                    $swappedLocationContentInfo,
                ],
            );

        $this->locationEventSubscriber->onSwapLocation($event);
    }

    public function testCallOnUnhideLocationMethod()
    {
        $event = $this->createMock(UnhideLocationEvent::class);
        $event
            ->expects(self::once())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->queryFactoryMock
            ->method('create')
            ->willReturn(new LocationQuery());

        $this->searchServiceMock
            ->method('findLocations')
            ->willReturn(new SearchResult([]));

        $this->locationServiceMock
            ->expects(self::atLeastOnce())
            ->method('loadLocation')
            ->willReturn($this->location);

        $this->contentHelperMock
            ->method('getIncludedContent')
            ->willReturn($this->content);

        $this->notificationServiceMock
            ->expects(self::once())
            ->method('sendNotification')
            ->withConsecutive(
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber::onUnhideLocation',
                    'UPDATE',
                    $this->location->getContentInfo(),
                ],
            );

        $this->locationEventSubscriber->onUnhideLocation($event);
    }

    public function testCallOnUpdateLocationMethod()
    {
        $event = $this->createMock(UpdateLocationEvent::class);
        $event
            ->expects(self::once())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->notificationServiceMock
            ->expects(self::once())
            ->method('sendNotification')
            ->withConsecutive(
                [
                    'EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber::onUpdateLocation',
                    'UPDATE',
                    $this->location->getContentInfo(),
                ],
            );

        $this->locationEventSubscriber->onUpdateLocation($event);
    }

    /**
     * @dataProvider updateLocationWithChildrenDataProvider
     */
    public function testUpdateSingleLocationWithChildren(
        string $event,
        string $method,
        string $expectedNotificationMethod,
        string $expectedNotificationType
    ) {
        $eventMock = $this->createMock($event);
        $eventMock
            ->expects(self::once())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->queryFactoryMock
            ->method('create')
            ->willReturn(new LocationQuery());

        $this->searchServiceMock
            ->method('findLocations')
            ->willReturnOnConsecutiveCalls(
                new SearchResult([
                    'searchHits' => [
                        new SearchHit(['valueObject' => $this->location1]),
                        new SearchHit(['valueObject' => $this->location2]),
                    ],
                ]),
                new SearchResult([]),
                new SearchResult([])
            );

        $this->locationServiceMock
            ->expects(self::at(0))
            ->method('loadLocation')
            ->with($this->equalTo($this->location1->id))
            ->willReturn($this->location1);

        $this->locationServiceMock
            ->expects(self::at(1))
            ->method('loadLocation')
            ->with($this->equalTo($this->location2->id))
            ->willReturn($this->location2);

        $this->locationServiceMock
            ->expects(self::at(2))
            ->method('loadLocation')
            ->with($this->equalTo($this->location->id))
            ->willReturn($this->location);

        $content1 = new Content([
            'versionInfo' => new VersionInfo([
                'contentInfo' => new ContentInfo([
                    'id' => self::CONTENT_ID + 1,
                    'contentTypeId' => self::CONTENT_TYPE_ID,
                ]),
            ]),
            'internalFields' => [],
        ]);
        $this->contentHelperMock
            ->expects(self::at(0))
            ->method('getIncludedContent')
            ->with($this->equalTo(self::CONTENT_ID + 1))
            ->willReturn($content1);

        $content2 = new Content([
            'versionInfo' => new VersionInfo([
                'contentInfo' => new ContentInfo([
                    'id' => self::CONTENT_ID + 2,
                    'contentTypeId' => self::CONTENT_TYPE_ID,
                ]),
            ]),
            'internalFields' => [],
        ]);
        $this->contentHelperMock
            ->expects(self::at(1))
            ->method('getIncludedContent')
            ->with($this->equalTo(3))
            ->willReturn($content2);

        $this->contentHelperMock
            ->expects(self::at(2))
            ->method('getIncludedContent')
            ->with($this->equalTo(self::CONTENT_ID))
            ->willReturn($this->content);

        $this->notificationServiceMock
            ->expects(self::exactly(3))
            ->method('sendNotification')
            ->withConsecutive(
                [
                    $expectedNotificationMethod,
                    $expectedNotificationType,
                    $content1->contentInfo,
                ],
                [
                    $expectedNotificationMethod,
                    $expectedNotificationType,
                    $content2->contentInfo,
                ],
                [
                    $expectedNotificationMethod,
                    $expectedNotificationType,
                    $this->content->contentInfo,
                ]
            );

        $this->locationEventSubscriber->$method($eventMock);
    }

    /**
     * @return array<array{
     *     class-string,
     *     string,
     *     string,
     *     \EzSystems\EzRecommendationClient\Value\EventNotification::ACTION_*,
     * }>
     */
    public function updateLocationWithChildrenDataProvider(): array
    {
        return [
            [
                HideLocationEvent::class,
                'onHideLocation',
                'EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber::hideLocation',
                'DELETE',
            ],
            [
                UnhideLocationEvent::class,
                'onUnhideLocation',
                'EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber::onUnhideLocation',
                'UPDATE',
            ],
        ];
    }
}
