<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use EzSystems\EzRecommendationClient\Event\RecommendationResponseEvent;
use EzSystems\EzRecommendationClient\Request\BasicRecommendationRequest as Request;
use EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface;
use EzSystems\EzRecommendationClient\SPI\RecommendationRequest;
use Ibexa\Personalization\Config\Repository\RepositoryConfigResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

final class RecommendationEventSubscriber implements EventSubscriberInterface
{
    private const DEFAULT_LOCALE = 'eng-GB';
    private const LOCALE_REQUEST_KEY = '_locale';

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface */
    private $localeConverter;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface */
    private $recommendationService;

    /** @var \Ibexa\Personalization\Config\Repository\RepositoryConfigResolverInterface */
    private $repositoryConfigResolver;

    public function __construct(
        LocaleConverterInterface $localeConverter,
        LoggerInterface $logger,
        RecommendationServiceInterface $recommendationService,
        RepositoryConfigResolverInterface $repositoryConfigResolver
    ) {
        $this->localeConverter = $localeConverter;
        $this->logger = $logger;
        $this->recommendationService = $recommendationService;
        $this->repositoryConfigResolver = $repositoryConfigResolver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RecommendationResponseEvent::class => ['onRecommendationResponse', 10],
        ];
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onRecommendationResponse(RecommendationResponseEvent $event): void
    {
        $recommendationRequest = $this->getRecommendationRequest($event->getParameterBag());

        $response = $this->recommendationService->getRecommendations($recommendationRequest);

        if (!$response) {
            return;
        }

        $event->setRecommendationItems($this->extractRecommendationItems($response));
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getRecommendationRequest(ParameterBag $parameterBag): RecommendationRequest
    {
        $contextItem = null;
        $content = $parameterBag->get(Request::CONTEXT_ITEMS_KEY);
        if ($content instanceof Content) {
            $contextItem = $this->repositoryConfigResolver->useRemoteId() ? $content->contentInfo->remoteId : $content->id;
        }

        return new Request([
            RecommendationRequest::SCENARIO => $parameterBag->get(RecommendationRequest::SCENARIO, ''),
            Request::LIMIT_KEY => $parameterBag->get(Request::LIMIT_KEY, 3),
            Request::CONTEXT_ITEMS_KEY => $contextItem,
            Request::CONTENT_TYPE_KEY => $content->getContentType()->id,
            Request::OUTPUT_TYPE_ID_KEY => $parameterBag->get(Request::OUTPUT_TYPE_ID_KEY),
            Request::CATEGORY_PATH_KEY => $this->getCategoryPath($content),
            Request::LANGUAGE_KEY => $this->getRequestLanguage($parameterBag->get(self::LOCALE_REQUEST_KEY)),
            Request::ATTRIBUTES_KEY => $parameterBag->get(Request::ATTRIBUTES_KEY, []),
            Request::FILTERS_KEY => $parameterBag->get(Request::FILTERS_KEY, []),
        ]);
    }

    private function getRequestLanguage(?string $locale): string
    {
        return $this->localeConverter->convertToEz($locale) ?? self::DEFAULT_LOCALE;
    }

    private function extractRecommendationItems(ResponseInterface $response): array
    {
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $this->logger->warning('RecommendationApi: StatusCode: ' . $response->getStatusCode() . ' Message: ' . $response->getReasonPhrase());
        }

        $recommendations = $response->getBody()->getContents();

        $recommendationItems = json_decode($recommendations, true);

        return $this->recommendationService->getRecommendationItems($recommendationItems['recommendationItems']);
    }

    private function getCategoryPath(Content $content): ?string
    {
        $mainLocation = $content->contentInfo->getMainLocation();
        if (null === $mainLocation) {
            return null;
        }

        $parentLocation = $mainLocation->getParentLocation();

        return null !== $parentLocation ? $parentLocation->pathString : null;
    }
}
