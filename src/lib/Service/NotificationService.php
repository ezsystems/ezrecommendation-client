<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service;

use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\SPI\Notification;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class NotificationService
{
    private const ALLOWED_OPTIONS_KEYS = [
        Notification::EVENTS_KEY,
        Notification::LICENSE_KEY,
        Notification::CUSTOMER_ID_KEY,
    ];

    /** @var \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface */
    protected $client;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    public function __construct(
        EzRecommendationClientInterface $client,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->logger = $logger;
    }

    abstract public function createNotification(array $options): Notification;

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(self::ALLOWED_OPTIONS_KEYS)
            ->setDefined(self::ALLOWED_OPTIONS_KEYS)
            ->setDefaults([
                Notification::EVENTS_KEY => [],
                Notification::CUSTOMER_ID_KEY => null,
                Notification::LICENSE_KEY => null,
            ])
            ->setAllowedTypes('events', 'array[]')
            ->setAllowedTypes('licenseKey', 'string')
            ->setAllowedTypes('customerId', 'int');
    }

    protected function send(Notification $notification, string $action): ?ResponseInterface
    {
        try {
            return $this->client->notifier()->notify($notification);
        } catch (RequestException $e) {
            $this->logger->error(sprintf('RecommendationNotifier: notification error for %s: %s', $action, $e->getMessage()));
        }

        return null;
    }
}
