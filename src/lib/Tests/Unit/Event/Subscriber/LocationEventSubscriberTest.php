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
use eZ\Publish\Core\Repository\Values\Content\Location;
use EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber;
use EzSystems\EzRecommendationClient\Tests\Common\Event\Subscriber\AbstractRepositoryEventSubscriberTest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocationEventSubscriberTest extends AbstractRepositoryEventSubscriberTest
{
    private const CONTENT_ID = 1;

    /** @var \EzSystems\EzRecommendationClient\Event\Subscriber\LocationEventSubscriber */
    private $locationEventSubscriber;

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
            ->with($this->location)
            ->willReturn($this->getLocationChildren());

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
            ->with($this->location)
            ->willReturn(new LocationList([
                'totalCount' => 0,
                'locations' => []
            ]));

        $this->locationServiceMock
            ->expects($this->atLeastOnce())
            ->method('loadLocation')
            ->willReturn($this->location);

        $this->locationEventSubscriber->onDeleteLocation($event);
    }
//
//    public function testCallOnDeleteLocationMethodWithChildren()
//    {
//        $event = $this->createMock(DeleteLocationEvent::class);
//        $event
//            ->expects($this->once())
//            ->method('getLocation')
//            ->willReturn($this->location);
//
//        $this->locationServiceMock
//            ->expects($this->at(0))
//            ->method('loadLocationChildren')
//            ->withAnyParameters()
//            ->willReturn($this->getLocationChildren());
//
//        $this->locationServiceMock
//            ->expects($this->at(0))
//            ->method('loadLocation')
//            ->willReturn($this->location);
//
//        $this->locationServiceMock
//            ->expects($this->at(1))
//            ->method('loadLocationChildren')
//            ->withAnyParameters()
//            ->willReturn($this->getEmptyLocationChildren());
//
//        $this->locationServiceMock
//            ->expects($this->at(1))
//            ->method('loadLocation')
//            ->willReturn(new Location([
//                'id' => 20,
//                'path' => ['1', '5', '20'],
//                'contentInfo' => new ContentInfo(['id' => self::CONTENT_ID + 1]),
//            ]));
//
//        $this->locationServiceMock
//            ->expects($this->at(2))
//            ->method('loadLocationChildren')
//            ->withAnyParameters()
//            ->willReturn($this->getEmptyLocationChildren());
//
//        $this->locationServiceMock
//            ->expects($this->at(2))
//            ->method('loadLocation')
//            ->willReturn(new Location([
//                'id' => 30,
//                'path' => ['1', '5', '30'],
//                'contentInfo' => new ContentInfo(['id' => self::CONTENT_ID + 2]),
//            ]));
//
//        $this->locationEventSubscriber->onDeleteLocation($event);
//    }

    public function testCallOnUpdateLocationMethod()
    {
        $event = $this->createMock(UpdateLocationEvent::class);
        $event
            ->expects($this->once())
            ->method('getLocation')
            ->willReturn($this->location);

        $this->locationEventSubscriber->onUpdateLocation($event);
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
            ->with($this->location)
            ->willReturn($this->getLocationChildren());

        $this->locationEventSubscriber->onMoveSubtree($event);
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
            ]
        ]);
    }


    private function getEmptyLocationChildren(): LocationList
    {
        return new LocationList([
            'totalCount' => 0,
            'locations' => []
        ]);
    }
}
