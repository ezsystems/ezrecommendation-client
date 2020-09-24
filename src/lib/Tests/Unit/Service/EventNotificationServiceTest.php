<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Unit\Service;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use EzSystems\EzRecommendationClient\API\Notifier;
use EzSystems\EzRecommendationClient\Config\ExportCredentialsResolver;
use EzSystems\EzRecommendationClient\Config\EzRecommendationClientCredentialsResolver;
use EzSystems\EzRecommendationClient\Helper\ContentHelper;
use EzSystems\EzRecommendationClient\Helper\ContentTypeHelper;
use EzSystems\EzRecommendationClient\Service\EventNotificationService;
use EzSystems\EzRecommendationClient\Tests\Common\Service\NotificationServiceTest;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use EzSystems\EzRecommendationClient\Value\Config\EzRecommendationClientCredentials;
use EzSystems\EzRecommendationClient\Value\EventNotification;

class EventNotificationServiceTest extends NotificationServiceTest
{
    /** @var \EzSystems\EzRecommendationClient\Service\EventNotificationService */
    private $notificationService;

    /** @var \EzSystems\EzRecommendationClient\Config\EzRecommendationClientCredentialsResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $credentialsResolverMock;

    /** @var \EzSystems\EzRecommendationClient\Config\ExportCredentialsResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $exportCredentialsMock;

    /** @var \EzSystems\EzRecommendationClient\Helper\ContentHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $contentHelperMock;

    /** @var \EzSystems\EzRecommendationClient\Helper\ContentTypeHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $contentTypeHelperMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->credentialsResolverMock = $this->createMock(EzRecommendationClientCredentialsResolver::class);
        $this->exportCredentialsMock = $this->createMock(ExportCredentialsResolver::class);
        $this->contentHelperMock = $this->createMock(ContentHelper::class);
        $this->contentTypeHelperMock = $this->createMock(ContentTypeHelper::class);
        $this->notificationService = new EventNotificationService(
            $this->clientMock,
            $this->loggerMock,
            $this->credentialsResolverMock,
            $this->exportCredentialsMock,
            $this->contentHelperMock,
            $this->contentTypeHelperMock
        );
    }

    public function testCreateInstanceOfEventNotificationService()
    {
        $this->assertInstanceOf(EventNotificationService::class, $this->notificationService);
    }

    public function testCreateEventNotification()
    {
        $this->assertInstanceOf(
            EventNotification::class,
            $this->notificationService->createNotification($this->basicNotificationOptions)
        );
    }

    public function testSendNotification()
    {
        $this->credentialsResolverMock
            ->expects($this->once())
            ->method('getCredentials')
            ->willReturn(new EzRecommendationClientCredentials([
                'customerId' => 12345,
                'licenseKey' => '12345-12345-12345-12345',
            ]));

        $this->exportCredentialsMock
            ->method('getCredentials')
            ->willReturn(new ExportCredentials([
                'login' => 12345,
                'password' => '12345-12345-12345-12345',
            ]));

        $this->clientMock
            ->method('__call')
            ->with(Notifier::API_NAME, [])
            ->willReturn($this->createMock(Notifier::class));

        $this->notificationService->sendNotification(
            'onHideLocation',
            EventNotification::ACTION_UPDATE,
            new ContentInfo([])
        );
    }
}
