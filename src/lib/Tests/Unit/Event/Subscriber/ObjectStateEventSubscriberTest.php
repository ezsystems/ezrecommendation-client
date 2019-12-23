<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Unit\Event\Subscriber;

use eZ\Publish\API\Repository\Events\ObjectState\SetContentStateEvent;
use EzSystems\EzRecommendationClient\Event\Subscriber\ObjectStateEventSubscriber;
use EzSystems\EzRecommendationClient\Tests\Common\Event\Subscriber\AbstractCoreEventSubscriberTest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ObjectStateEventSubscriberTest extends AbstractCoreEventSubscriberTest
{
    /** @var \EzSystems\EzRecommendationClient\Event\Subscriber\ObjectStateEventSubscriber */
    private $objectStateEventSubscriber;

    public function setUp(): void
    {
        parent::setUp();

        $this->objectStateEventSubscriber = new ObjectStateEventSubscriber($this->notificationServiceMock);
    }

    public function testCreateInstanceOfObjectStateEventSubscriber()
    {
        $this->assertInstanceOf(ObjectStateEventSubscriber::class, $this->objectStateEventSubscriber);
    }

    public function getEventSubscriber(): EventSubscriberInterface
    {
        return $this->objectStateEventSubscriber;
    }

    public function subscribedEventsDataProvider(): array
    {
        return [
            [SetContentStateEvent::class]
        ];
    }

    public function testCallOnSetContentStateMethod()
    {
        $event = $this->createMock(SetContentStateEvent::class);
        $event
            ->expects($this->once())
            ->method('getContentInfo')
            ->willReturn($this->contentInfo);

        $this->objectStateEventSubscriber->onSetContentState($event);
    }
}
