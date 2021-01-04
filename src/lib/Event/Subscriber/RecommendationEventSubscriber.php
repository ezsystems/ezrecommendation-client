<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use EzSystems\EzRecommendationClient\Event\RecommendationResponseEvent;
use EzSystems\EzRecommendationClient\Helper\ContentHelper;
use EzSystems\EzRecommendationClient\Request\BasicRecommendationRequest as Request;
use EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface;
use EzSystems\EzRecommendationClient\SPI\RecommendationRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

final class RecommendationEventSubscriber implements EventSubscriberInterface
{
    private const LOCALE_REQUEST_KEY = '_locale';
    private const DEFAULT_LOCALE = 'eng-GB';

    /** @var \EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface */
    private $recommendationService;

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface */
    private $localeConverter;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \EzSystems\EzRecommendationClient\Helper\ContentHelper */
    private $contentHelper;

    /**
     * @param \EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface $recommendationService
     * @param \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface $localeConverter
     * @param \Psr\Log\LoggerInterface $logger
     * @param \EzSystems\EzRecommendationClient\Helper\ContentHelper $contentHelper
     */
    public function __construct(
        RecommendationServiceInterface $recommendationService,
        LocaleConverterInterface $localeConverter,
        LoggerInterface $logger,
        ContentHelper $contentHelper
    ) {
        $this->recommendationService = $recommendationService;
        $this->contentHelper = $contentHelper;
        $this->localeConverter = $localeConverter;
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
     *
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
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
     *
     * @return \EzSystems\EzRecommendationClient\SPI\RecommendationRequest
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getRecommendationRequest(ParameterBag $parameterBag): RecommendationRequest
    {
        $contextItems = (int) $parameterBag->get(Request::CONTEXT_ITEMS_KEY, 0);

        return new Request([
            RecommendationRequest::SCENARIO => $parameterBag->get(RecommendationRequest::SCENARIO, ''),
            Request::LIMIT_KEY => $parameterBag->get(Request::LIMIT_KEY, 3),
            Request::CONTEXT_ITEMS_KEY => $contextItems,
            Request::CONTENT_TYPE_KEY => $this->contentHelper->getContentTypeId($this->contentHelper->getContentIdentifier($contextItems)),
            Request::OUTPUT_TYPE_ID_KEY => $this->contentHelper->getContentTypeId($parameterBag->get(Request::OUTPUT_TYPE_ID_KEY, '')),
            Request::CATEGORY_PATH_KEY => $this->contentHelper->getLocationPathString($contextItems),
            Request::LANGUAGE_KEY => $this->getRequestLanguage($parameterBag->get(self::LOCALE_REQUEST_KEY)),
            Request::ATTRIBUTES_KEY => $parameterBag->get(Request::ATTRIBUTES_KEY, []),
            Request::FILTERS_KEY => $parameterBag->get(Request::FILTERS_KEY, []),
        ]);
    }

    /**
     * @param string|null $locale
     *
     * @return string
     */
    private function getRequestLanguage(?string $locale): string
    {
        return $this->localeConverter->convertToEz($locale) ?? self::DEFAULT_LOCALE;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
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
