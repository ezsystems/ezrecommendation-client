<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use EzSystems\EzRecommendationClient\Event\RecommendationResponseEvent;
use EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class RecommendationEventSubscriber implements EventSubscriberInterface
{
    /** @var \EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface */
    private $recommendationService;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @param \EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface $recommendationService
     * @param \\Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        RecommendationServiceInterface $recommendationService,
        LoggerInterface $logger
    ) {
        $this->recommendationService = $recommendationService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RecommendationResponseEvent::NAME => ['onRecommendationResponse', 10],
        ];
    }

    /**
     * @param \EzSystems\EzRecommendationClient\Event\RecommendationResponseEvent $event
     */
    public function onRecommendationResponse(RecommendationResponseEvent $event): void
    {
        $response = $this->recommendationService->getRecommendations($event->getParameterBag());

        if (!$response) {
            return;
        }

        $event->setRecommendationItems($this->extractRecommendationItems($response));
    }

    /**
     * @param ResponseInterface $response
     *
     * @return array
     */
    private function extractRecommendationItems(ResponseInterface $response): array
    {
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $this->logger->warning('RecommendationApi: StatusCode: ' . $response->getStatusCode() . ' Message: ' . $response->getReasonPhrase());
        }

        $recommendations = $response->getBody()->getContents();

        $recommendationItems = json_decode($recommendations, true);

        return $this->recommendationService->getRecommendationItems($recommendationItems['recommendationItems']);
    }
}
