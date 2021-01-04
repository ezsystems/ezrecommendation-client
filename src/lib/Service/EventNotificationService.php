<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use EzSystems\EzRecommendationClient\Helper\ContentHelper;
use EzSystems\EzRecommendationClient\Helper\ContentTypeHelper;
use EzSystems\EzRecommendationClient\Request\EventNotifierRequest;
use EzSystems\EzRecommendationClient\SPI\Notification;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use EzSystems\EzRecommendationClient\Value\EventNotification;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EventNotificationService extends NotificationService
{
    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface */
    private $clientCredentials;

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface */
    private $exportCredentials;

    /** @var \EzSystems\EzRecommendationClient\Helper\ContentHelper */
    private $contentHelper;

    /** @var \EzSystems\EzRecommendationClient\Helper\ContentTypeHelper */
    private $contentTypeHelper;

    public function __construct(
        EzRecommendationClientInterface $client,
        LoggerInterface $logger,
        CredentialsResolverInterface $clientCredentials,
        CredentialsResolverInterface $exportCredentials,
        ContentHelper $contentHelper,
        ContentTypeHelper $contentTypeHelper
    ) {
        parent::__construct($client, $logger);

        $this->clientCredentials = $clientCredentials;
        $this->exportCredentials = $exportCredentials;
        $this->contentHelper = $contentHelper;
        $this->contentTypeHelper = $contentTypeHelper;
    }

    /**
     * @throws \Exception
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function sendNotification(string $method, string $action, ContentInfo $contentInfo): void
    {
        $credentials = $this->clientCredentials->getCredentials();

        if (!$credentials || $this->contentTypeHelper->isContentTypeExcluded($contentInfo)) {
            return;
        }

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $notificationOptions = $resolver->resolve([
            'events' => $this->generateNotificationEvents($action, $contentInfo, $this->exportCredentials->getCredentials()),
            'licenseKey' => $credentials->getLicenseKey(),
            'customerId' => $credentials->getCustomerId(),
        ]);

        $this->send(
            $this->createNotification($notificationOptions),
            $method
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createNotification(array $options): Notification
    {
        $notification = new EventNotification();
        $notification->events = $options['events'];
        $notification->customerId = $options['customerId'];
        $notification->licenseKey = $options['licenseKey'];

        return $notification;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function generateNotificationEvents(
        string $action,
        ContentInfo $contentInfo,
        ExportCredentials $exportCredentials
    ): array {
        $events = [];

        foreach ($this->contentHelper->getLanguageCodes($contentInfo) as $lang) {
            $event = new EventNotifierRequest([
                EventNotifierRequest::ACTION_KEY => $action,
                EventNotifierRequest::FORMAT_KEY => 'EZ',
                EventNotifierRequest::URI_KEY => $this->contentHelper->getContentUri($contentInfo, $lang),
                EventNotifierRequest::ITEM_ID_KEY => $contentInfo->id,
                EventNotifierRequest::CONTENT_TYPE_ID_KEY => $contentInfo->contentTypeId,
                EventNotifierRequest::LANG_KEY => $lang ?? null,
                EventNotifierRequest::CREDENTIALS_KEY => [
                    'login' => $exportCredentials->getLogin(),
                    'password' => $exportCredentials->getPassword(),
                ],
            ]);

            $events[] = $event->getRequestAttributes();
        }

        return $events;
    }
}
