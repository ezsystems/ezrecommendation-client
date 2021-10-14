<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Service;

use EzSystems\EzRecommendationClient\API\Notifier;
use EzSystems\EzRecommendationClient\Service\ExportNotificationService;
use EzSystems\EzRecommendationClient\Value\ExportNotification;
use GuzzleHttp\Psr7\Response;
use Ibexa\PersonalizationClient\Value\Export\Parameters;

class ExportNotificationServiceTest extends NotificationServiceTest
{
    /** @var \EzSystems\EzRecommendationClient\Service\ExportNotificationService */
    private $notificationService;

    /** @var array<array<string, array<string, array<string>|string>>> */
    private $urls;

    /** @var array<string> */
    private $notificationOptions;

    /** @var \Ibexa\PersonalizationClient\Value\Export\Parameters */
    private $exportParameters;

    public function setUp(): void
    {
        parent::setUp();

        $this->notificationService = new ExportNotificationService(
            $this->clientMock,
            $this->loggerMock
        );
        $this->exportParameters = Parameters::fromArray([
            'customer_id' => '12345',
            'license_key' => '12345-12345-12345-12345',
            'siteaccess' => 'test',
            'item_type_identifier_list' => 'article, product, blog',
            'languages' => 'eng-GB',
            'web_hook' => 'https://reco-engine.com/api/12345/items',
            'host' => 'https://127.0.0.1',
            'page_size' => '500',
        ]);
        $this->urls = [
            1 => [
                'eng-EN' => [
                    'contentTypeName' => 'article',
                    'urlList' => ['uri1', 'uri2', 'uri3'],
                ],
                'fre-FR' => [
                    'contentTypeName' => 'article',
                    'urlList' => ['uri1', 'uri2', 'uri3'],
                ],
                'ger-DE' => [
                    'contentTypeName' => 'article',
                    'urlList' => ['uri1', 'uri2', 'uri3'],
                ],
            ],
            2 => [
                'eng-EN' => [
                    'contentTypeName' => 'blog_post',
                    'urlList' => ['uri1', 'uri2', 'uri3'],
                ],
                'fre-FR' => [
                    'contentTypeName' => 'blog_post',
                    'urlList' => ['uri1', 'uri2', 'uri3'],
                ],
                'ger-DE' => [
                    'contentTypeName' => 'blog_post',
                    'urlList' => ['uri1', 'uri2', 'uri3'],
                ],
            ],
        ];
        $this->notificationOptions = array_merge(
            $this->basicNotificationOptions,
            ['webHook' => 'webHook.uri']
        );
    }

    public function testCreateInstanceOfEventNotificationService(): void
    {
        $this->assertInstanceOf(ExportNotificationService::class, $this->notificationService);
    }

    public function testCreateExportNotification(): void
    {
        $this->assertInstanceOf(
            ExportNotification::class,
            $this->notificationService->createNotification($this->notificationOptions)
        );
    }

    public function testSendNotification(): void
    {
        $notifier = $this->createMock(Notifier::class);
        $this->clientMock
            ->expects(self::once())
            ->method('__call')
            ->with(Notifier::API_NAME, [])
            ->willReturn($notifier);

        $notifier
            ->expects(self::once())
            ->method('notify')
            ->willReturn(new Response());

        self::assertEquals(
            new Response(),
            $this->notificationService->sendNotification(
                $this->exportParameters,
                $this->urls,
                ['login' => 'abc', 'pass' => 'def']
            )
        );
    }
}
