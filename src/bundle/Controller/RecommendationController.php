<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Controller;

use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use EzSystems\EzRecommendationClient\Event\RecommendationResponseEvent;
use EzSystems\EzRecommendationClient\Request\BasicRecommendationRequest;
use EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class RecommendationController
{
    private const DEFAULT_TEMPLATE = '@EzRecommendationClient/recommendations.html.twig';

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    /** @var \EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface */
    private $recommendationService;

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface */
    private $credentialsResolver;

    /** @var \Twig\Environment */
    private $twig;

    /** @var bool */
    private $sendDeliveryFeedback = true;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RecommendationServiceInterface $recommendationService,
        CredentialsResolverInterface $credentialsResolver,
        Environment $twig
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->recommendationService = $recommendationService;
        $this->credentialsResolver = $credentialsResolver;
        $this->twig = $twig;
    }

    /**
     * @throws \Twig\Error\Error
     */
    public function showRecommendationsAction(Request $request): Response
    {
        if (!$this->credentialsResolver->hasCredentials()) {
            return new Response();
        }

        $event = new RecommendationResponseEvent($request->attributes);
        $this->eventDispatcher->dispatch($event);

        if (!$event->getRecommendationItems()) {
            return new Response();
        }

        $template = $this->getTemplate($request->get('template'));

        $response = new Response();
        $response->setPrivate();

        if ($this->sendDeliveryFeedback) {
            $this->recommendationService->sendDeliveryFeedback($request->get(BasicRecommendationRequest::OUTPUT_TYPE_ID_KEY));
        }

        return $response->setContent(
            $this->twig()->render($template, [
            'recommendations' => $event->getRecommendationItems(),
            'templateId' => Uuid::uuid4()->toString(),
            ])
        );
    }

    public function sendDeliveryFeedback(bool $value): void
    {
        $this->sendDeliveryFeedback = $value;
    }

    protected function twig(): Environment
    {
        return $this->twig;
    }

    private function getTemplate(?string $template): string
    {
        return $this->twig()->getLoader()->exists($template) ? $template : self::DEFAULT_TEMPLATE;
    }
}
