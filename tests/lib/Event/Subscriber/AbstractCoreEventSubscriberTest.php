<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Event\Subscriber;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use EzSystems\EzRecommendationClient\Service\EventNotificationService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractCoreEventSubscriberTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\EzSystems\EzRecommendationClient\Service\EventNotificationService */
    protected $notificationServiceMock;

    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    protected $contentInfo;

    /** @var \eZ\Publish\Core\Repository\Values\Content\Location */
    protected $location;

    /** @var \eZ\Publish\Core\Repository\Values\Content\Content */
    protected $content;

    public function setUp(): void
    {
        $this->notificationServiceMock = $this->createMock(EventNotificationService::class);
        $this->contentInfo = new ContentInfo([
            'id' => 1,
            'contentTypeId' => 2,
        ]);
        $this->location = new Location([
            'id' => 1,
            'path' => ['1', '5'],
            'contentInfo' => $this->contentInfo,
        ]);
        $this->content = new Content([
            'versionInfo' => new VersionInfo([
                'contentInfo' => $this->contentInfo,
            ]),
        ]);
    }

    /**
     * @dataProvider subscribedEventsDataProvider
     */
    public function testHasSubscribedEvent(string $event)
    {
        $this->assertArrayHasKey($event, $this->getEventSubscriber()::getSubscribedEvents());
    }

    abstract public function getEventSubscriber(): EventSubscriberInterface;

    abstract public function subscribedEventsDataProvider(): array;
}
