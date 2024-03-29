<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Controller;

use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use EzSystems\EzRecommendationClient\Event\RecommendationResponseEvent;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Twig\Environment;

class RecommendationController
{
    private const DEFAULT_TEMPLATE = '@EzRecommendationClient/recommendations.html.twig';

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface */
    private $credentialsResolver;

    /** @var \Symfony\WebpackEncoreBundle\Asset\TagRenderer */
    private $encoreTagRenderer;

    /** @var \Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface */
    private $entrypointLookupCollection;

    /** @var \Twig\Environment */
    private $twig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        CredentialsResolverInterface $credentialsResolver,
        TagRenderer $encoreTagRenderer,
        EntrypointLookupCollectionInterface $entrypointLookupCollection,
        Environment $twig
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->credentialsResolver = $credentialsResolver;
        $this->encoreTagRenderer = $encoreTagRenderer;
        $this->entrypointLookupCollection = $entrypointLookupCollection;
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

        $this->encoreTagRenderer->reset();
        $this->entrypointLookupCollection->getEntrypointLookup('ezplatform')->reset();

        $response->setContent(
            $this->twig()->render($template, [
            'recommendations' => $event->getRecommendationItems(),
            'templateId' => Uuid::uuid4()->toString(),
            ])
        );

        $this->encoreTagRenderer->reset();
        $this->entrypointLookupCollection->getEntrypointLookup('ezplatform')->reset();

        return $response;
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
