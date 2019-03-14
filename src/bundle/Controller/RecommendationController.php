<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Controller;

use EzSystems\EzRecommendationClient\Event\RecommendationResponseEvent;
use EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface;
use EzSystems\EzRecommendationClient\Value\RecommendationMetadata;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class RecommendationController
{
    private const DEFAULT_TEMPLATE = 'EzRecommendationClientBundle::recommendations.html.twig';

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    /** @var \EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface */
    private $recommendationService;

    /** @var \Symfony\Bundle\TwigBundle\TwigEngine */
    protected $templateEngine;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var bool */
    private $sendDeliveryFeedback = true;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface $recommendationService
     * @param \Symfony\Component\Templating\EngineInterface $templateEngine
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RecommendationServiceInterface $recommendationService,
        EngineInterface $templateEngine,
        LoggerInterface $logger
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->recommendationService = $recommendationService;
        $this->templateEngine = $templateEngine;
        $this->logger = $logger;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Twig\Error\Error
     */
    public function showRecommendationsAction(Request $request): Response
    {
        $event = new RecommendationResponseEvent($request->attributes);
        $this->eventDispatcher->dispatch(RecommendationResponseEvent::NAME, $event);

        if (!$event->getRecommendationItems()) {
            return new Response();
        }

        $template = $this->getTemplate($request->get('template'));

        $response = new Response();
        $response->setPrivate();

        if ($this->sendDeliveryFeedback) {
            $this->recommendationService->sendDeliveryFeedback($request->get(RecommendationMetadata::OUTPUT_TYPE_ID));
        }

        return $this->templateEngine->renderResponse($template, [
            'recommendations' => $event->getRecommendationItems(),
            'templateId' => Uuid::uuid4()->toString(),
            ],
            $response
        );
    }

    /**
     * @param bool $value
     */
    public function sendDeliveryFeedback(bool $value): void
    {
        $this->sendDeliveryFeedback = $value;
    }

    /**
     * @param null|string $template
     *
     * @return string
     */
    private function getTemplate(?string $template): string
    {
        return $this->templateEngine->exists($template) ? $template : self::DEFAULT_TEMPLATE;
    }
}
