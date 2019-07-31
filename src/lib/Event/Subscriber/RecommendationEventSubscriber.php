<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use EzSystems\EzRecommendationClient\Event\RecommendationResponseEvent;
use EzSystems\EzRecommendationClient\Helper\ContentHelper;
use EzSystems\EzRecommendationClient\Request\BasicRecommendationRequest;
use EzSystems\EzRecommendationClient\Service\RecommendationServiceInterface;
use EzSystems\EzRecommendationClient\SPI\RecommendationRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

class RecommendationEventSubscriber implements EventSubscriberInterface
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
        $contextItems = (int) $parameterBag->get(BasicRecommendationRequest::CONTEXT_ITEMS_KEY, 0);

        return new BasicRecommendationRequest([
            RecommendationRequest::SCENARIO => $parameterBag->get(RecommendationRequest::SCENARIO, ''),
            BasicRecommendationRequest::LIMIT_KEY => $parameterBag->get(BasicRecommendationRequest::LIMIT_KEY, 3),
            BasicRecommendationRequest::CONTEXT_ITEMS_KEY => $contextItems,
            BasicRecommendationRequest::CONTENT_TYPE_KEY => $this->contentHelper->getContentTypeId($this->contentHelper->getContentIdentifier($contextItems)),
            BasicRecommendationRequest::OUTPUT_TYPE_ID_KEY => $this->contentHelper->getContentTypeId($parameterBag->get(BasicRecommendationRequest::OUTPUT_TYPE_ID_KEY, '')),
            BasicRecommendationRequest::CATEGORY_PATH_KEY => $this->contentHelper->getLocationPathString($contextItems),
            BasicRecommendationRequest::LANGUAGE_KEY => $parameterBag->get($this->localeConverter->convertToEz(self::LOCALE_REQUEST_KEY), self::DEFAULT_LOCALE),
            BasicRecommendationRequest::ATTRIBUTES_KEY => $parameterBag->get(BasicRecommendationRequest::ATTRIBUTES_KEY, []),
            BasicRecommendationRequest::FILTERS_KEY => $parameterBag->get(BasicRecommendationRequest::FILTERS_KEY, []),
        ]);
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
