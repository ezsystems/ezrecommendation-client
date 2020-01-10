<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Unit\Service;

use EzSystems\EzRecommendationClient\Api\Notifier;
use EzSystems\EzRecommendationClient\Service\ExportNotificationService;
use EzSystems\EzRecommendationClient\Tests\Common\Service\NotificationServiceTest;
use EzSystems\EzRecommendationClient\Value\ExportNotification;
use EzSystems\EzRecommendationClient\Value\ExportParameters;

class ExportNotificationServiceTest extends NotificationServiceTest
{
    /** @var \EzSystems\EzRecommendationClient\Service\ExportNotificationService */
    private $notificationService;

    /** @var \EzSystems\EzRecommendationClient\Value\ExportParameters|\PHPUnit\Framework\MockObject\MockObject */
    private $exportParametersMock;

    /** @var array */
    private $urls;

    /** @var array */
    private $notificationOptions;

    public function setUp(): void
    {
        parent::setUp();

        $this->notificationService = new ExportNotificationService(
            $this->clientMock,
            $this->loggerMock
        );
        $this->exportParametersMock = $this->createMock(ExportParameters::class);
        $this->urls = [
            1 => [
                'eng-EN' => [
                    'contentTypeName' => 'article',
                    'urlList' => ['uri1', 'uri2', 'uri3']
                ],
                'fre-FR' => [
                    'contentTypeName' => 'article',
                    'urlList' => ['uri1', 'uri2', 'uri3']
                ],
                'ger-DE' => [
                    'contentTypeName' => 'article',
                    'urlList' => ['uri1', 'uri2', 'uri3']
                ],
            ],
            2 => [
                'eng-EN' => [
                    'contentTypeName' => 'blog_post',
                    'urlList' => ['uri1', 'uri2', 'uri3']
                ],
                'fre-FR' => [
                    'contentTypeName' => 'blog_post',
                    'urlList' => ['uri1', 'uri2', 'uri3']
                ],
                'ger-DE' => [
                    'contentTypeName' => 'blog_post',
                    'urlList' => ['uri1', 'uri2', 'uri3']
                ],
            ],
        ];
        $this->notificationOptions = array_merge(
            $this->basicNotificationOptions,
            ['webHook' => 'webHook.uri']
        );
    }

    public function testCreateInstanceOfEventNotificationService()
    {
        $this->assertInstanceOf(ExportNotificationService::class, $this->notificationService);
    }

    public function testCreateExportNotification()
    {
        $this->assertInstanceOf(
            ExportNotification::class,
            $this->notificationService->createNotification($this->notificationOptions)
        );
    }

    public function testSendNotification()
    {
        $this->exportParametersMock
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn($this->notificationOptions);

        $this->clientMock
            ->method('__call')
            ->with(Notifier::API_NAME, [])
            ->willReturn($this->createMock(Notifier::class));

        $this->notificationService->sendNotification(
            $this->exportParametersMock,
            $this->urls,
            ['login' => 'abc', 'pass' => 'def']
        );
    }
}
