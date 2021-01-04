<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Controller;

use EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface;
use EzSystems\EzRecommendationClient\Event\RecommendationResponseEvent;
use EzSystems\EzRecommendationClient\Request\BasicRecommendationRequest;
use EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface;
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

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface */
    private $credentialsChecker;

    /** @var \Symfony\Bundle\TwigBundle\TwigEngine */
    protected $templateEngine;

    /** @var bool */
    private $sendDeliveryFeedback = true;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface $recommendationService
     * @param \EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface $credentialsChecker
     * @param \Symfony\Component\Templating\EngineInterface $templateEngine
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RecommendationServiceInterface $recommendationService,
        CredentialsCheckerInterface $credentialsChecker,
        EngineInterface $templateEngine
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->recommendationService = $recommendationService;
        $this->credentialsChecker = $credentialsChecker;
        $this->templateEngine = $templateEngine;
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
        if (!$this->credentialsChecker->hasCredentials()) {
            return new Response();
        }

        $event = new RecommendationResponseEvent($request->attributes);
        $this->eventDispatcher->dispatch(RecommendationResponseEvent::NAME, $event);

        if (!$event->getRecommendationItems()) {
            return new Response();
        }

        $template = $this->getTemplate($request->get('template'));

        $response = new Response();
        $response->setPrivate();

        if ($this->sendDeliveryFeedback) {
            $this->recommendationService->sendDeliveryFeedback($request->get(BasicRecommendationRequest::OUTPUT_TYPE_ID_KEY));
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
     * @param string|null $template
     *
     * @return string
     */
    private function getTemplate(?string $template): string
    {
        return $this->templateEngine->exists($template) ? $template : self::DEFAULT_TEMPLATE;
    }
}
