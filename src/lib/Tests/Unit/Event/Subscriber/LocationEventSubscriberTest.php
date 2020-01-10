<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Unit\Event\Subscriber;

use eZ\Publish\API\Repository\Events\Location\CopySubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\CreateLocationEvent;
use eZ\Publish\API\Repository\Events\Location\DeleteLocationEvent;
use eZ\Publish\API\Repository\Events\Location\HideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\MoveSubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\SwapLocationEvent;
use eZ\Publish\API\Repository\Events\Location\UnhideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\UpdateLocationEvent;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationList;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber;
use EzSystems\EzRecommendationClient\Tests\Common\Event\Subscriber\AbstractRepositoryEventSubscriberTest;
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

    /** @var \eZ\Publish\API\Repository\Values\Content\LocationList */
    private $emptyLocationChildren;

    /** @var \eZ\Publish\API\Repository\Values\Content\LocationList */
    private $locationChildren;

    public function setUp(): void
    {
        parent::setUp();

        $this->locationEventSubscriber = new LocationEventSubscriber(
            $this->notificationServiceMock,
            $this->contentServiceMock,
            $this->locationServiceMock,
            $this->locationHelperMock,
            $this->contentHelperMock
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
        $this->emptyLocationChildren = new LocationList([
            'totalCount' => 0,
            'locations' => []
        ]);
        $this->locationChildren = new LocationList([
            'totalCount' => 2,
            'locations' => [
                $this->location1,
                $this->location2,
            ]
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
            [DeleteLocationEvent::class],
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
            ->expects($this->once())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->locationServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadLocationChildren')
            ->with($this->equalTo($this->location))
            ->willReturn($this->locationChildren);

        $this->locationEventSubscriber->onCopySubtree($event);
    }

    public function testCallOnCreateLocationMethod()
    {
        $event = $this->createMock(CreateLocationEvent::class);
        $event
            ->expects($this->once())
            ->method('getContentInfo')
            ->willReturn($this->contentInfo);

        $this->locationEventSubscriber->onCreateLocation($event);
    }

    public function testCallOnDeleteLocationMethod()
    {
        $event = $this->createMock(DeleteLocationEvent::class);
        $event
            ->expects($this->once())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->locationServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadLocationChildren')
            ->with($this->equalTo($this->location))
            ->willReturn($this->emptyLocationChildren);

        $this->locationServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadLocation')
            ->willReturn($this->location);

        $this->locationEventSubscriber->onDeleteLocation($event);
    }

    public function testCallOnHideLocationMethod()
    {
        $event = $this->createMock(HideLocationEvent::class);
        $event
            ->expects($this->once())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->locationServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadLocationChildren')
            ->with($this->equalTo($this->location))
            ->willReturn($this->emptyLocationChildren);

        $this->locationServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadLocation')
            ->willReturn($this->location);

        $this->contentHelperMock
            ->expects($this->atLeastOnce())
            ->method('getIncludedContent')
            ->willReturn($this->content);

        $this->locationEventSubscriber->onHideLocation($event);
    }

    public function testCallOnMoveSubtreeMethod()
    {
        $event = $this->createMock(MoveSubtreeEvent::class);
        $event
            ->expects($this->once())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->locationServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadLocationChildren')
            ->with($this->equalTo($this->location))
            ->willReturn($this->locationChildren);

        $this->locationEventSubscriber->onMoveSubtree($event);
    }

    public function testCallOnSwapLocationMethod()
    {
        $swappedLocation = new Location([
            'id' => 120,
            'path' => ['1', '5', '120'],
            'contentInfo' => new ContentInfo(['id' => self::CONTENT_ID + 120]),
        ]);

        $event = $this->createMock(SwapLocationEvent::class);
        $event
            ->expects($this->once())
            ->method('getLocation1')
            ->willReturn($this->location);
        $event
            ->expects($this->once())
            ->method('getLocation2')
            ->willReturn($swappedLocation);

        $this->locationServiceMock
            ->expects($this->at(0))
            ->method('loadLocationChildren')
            ->with($this->equalTo($this->location))
            ->willReturn($this->emptyLocationChildren);

        $this->locationServiceMock
            ->expects($this->at(1))
            ->method('loadLocation')
            ->willReturn($this->location);

        $this->locationServiceMock
            ->expects($this->at(2))
            ->method('loadLocationChildren')
            ->with($this->equalTo($swappedLocation))
            ->willReturn($this->emptyLocationChildren);

        $this->locationServiceMock
            ->expects($this->at(3))
            ->method('loadLocation')
            ->willReturn($swappedLocation);

        $this->locationEventSubscriber->onSwapLocation($event);
    }

    public function testCallOnUnhideLocationMethod()
    {
        $event = $this->createMock(UnhideLocationEvent::class);
        $event
            ->expects($this->once())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->locationServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadLocationChildren')
            ->with($this->equalTo($this->location))
            ->willReturn($this->emptyLocationChildren);

        $this->locationServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadLocation')
            ->willReturn($this->location);

        $this->locationEventSubscriber->onUnhideLocation($event);
    }

    public function testCallOnUpdateLocationMethod()
    {
        $event = $this->createMock(UpdateLocationEvent::class);
        $event
            ->expects($this->once())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->locationEventSubscriber->onUpdateLocation($event);
    }

    /**
     * @dataProvider updateLocationWithChildrenDataProvider
     */
    public function testUpdateSingleLocationWithChildren(string $event, string $method)
    {
        $eventMock = $this->createMock($event);
        $eventMock
            ->expects($this->once())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->locationServiceMock
            ->expects($this->at(0))
            ->method('loadLocationChildren')
            ->with($this->equalTo($this->location))
            ->willReturn($this->locationChildren);

        $this->locationServiceMock
            ->expects($this->at(1))
            ->method('loadLocationChildren')
            ->with($this->equalTo($this->location1))
            ->willReturn($this->emptyLocationChildren);

        $this->locationServiceMock
            ->expects($this->at(2))
            ->method('loadLocation')
            ->with($this->equalTo($this->location1->id))
            ->willReturn($this->location1);

        $this->locationServiceMock
            ->expects($this->at(3))
            ->method('loadLocationChildren')
            ->with($this->equalTo($this->location2))
            ->willReturn($this->emptyLocationChildren);

        $this->locationServiceMock
            ->expects($this->at(4))
            ->method('loadLocation')
            ->with($this->equalTo($this->location2->id))
            ->willReturn($this->location2);

        $this->locationServiceMock
            ->expects($this->at(5))
            ->method('loadLocation')
            ->with($this->equalTo($this->location->id))
            ->willReturn($this->location);

        $this->contentHelperMock
            ->expects($this->at(0))
            ->method('getIncludedContent')
            ->with($this->equalTo(self::CONTENT_ID + 1))
            ->willReturn(new Content([
                'versionInfo' => new VersionInfo([
                    'contentInfo' => new ContentInfo([
                        'id' => self::CONTENT_ID + 1,
                        'contentTypeId' => self::CONTENT_TYPE_ID,
                    ]),
                ]),
                'internalFields' => [],
            ]));

        $this->contentHelperMock
            ->expects($this->at(1))
            ->method('getIncludedContent')
            ->with($this->equalTo(3))
            ->willReturn(new Content([
                'versionInfo' => new VersionInfo([
                    'contentInfo' => new ContentInfo([
                        'id' => self::CONTENT_ID + 2,
                        'contentTypeId' => self::CONTENT_TYPE_ID,
                    ]),
                ]),
                'internalFields' => [],
            ]));

        $this->contentHelperMock
            ->expects($this->at(2))
            ->method('getIncludedContent')
            ->with($this->equalTo(self::CONTENT_ID))
            ->willReturn($this->content);

        $this->locationEventSubscriber->$method($eventMock);
    }

    public function updateLocationWithChildrenDataProvider(): array
    {
        return [
            [DeleteLocationEvent::class, 'onDeleteLocation'],
            [HideLocationEvent::class, 'onHideLocation'],
            [UnhideLocationEvent::class, 'onUnhideLocation'],
        ];
    }
}
