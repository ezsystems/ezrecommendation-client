<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service;

use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Helper\ContentHelper;
use EzSystems\EzRecommendationClient\Value\RecommendationItem;
use EzSystems\EzRecommendationClient\Value\RecommendationMetadata;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class RecommendationService implements RecommendationServiceInterface
{
    private const LOCALE_REQUEST_KEY = '_locale';
    private const DEFAULT_LOCALE = 'eng-GB';

    /** @var \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface */
    private $client;

    /** @param \EzSystems\EzRecommendationClient\Service\UserServiceInterface */
    private $userService;

    /** @var \EzSystems\EzRecommendationClient\Helper\ContentHelper */
    private $contentHelper;

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface */
    private $localeConverter;

    /**
     * @param \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface $client
     * @param \EzSystems\EzRecommendationClient\Service\UserServiceInterface $userService
     * @param \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface $localeConverter
     * @param \EzSystems\EzRecommendationClient\Helper\ContentHelper $contentHelper
     */
    public function __construct(
        EzRecommendationClientInterface $client,
        UserServiceInterface $userService,
        LocaleConverterInterface $localeConverter,
        ContentHelper $contentHelper
    ) {
        $this->client = $client;
        $this->userService = $userService;
        $this->localeConverter = $localeConverter;
        $this->contentHelper = $contentHelper;

        $this->client->setUserIdentifier($this->userService->getUserIdentifier());
    }

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getRecommendations(ParameterBag $parameterBag): ?ResponseInterface
    {
        $recommendationMetadata = new RecommendationMetadata(
            $this->getRecommendationMetadataParameters($parameterBag)
        );

        return $this->client
            ->recommendation()
            ->getRecommendations($recommendationMetadata);
    }

    /**
     * {@inheritdoc}
     */
    public function sendDeliveryFeedback(string $outputContentType): void
    {
        $this->client
            ->eventTracking()
            ->sendNotificationPing($outputContentType);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecommendationItems(array $recommendationItems): array
    {
        $recommendationCollection = [];

        $recommendationItemPrototype = new RecommendationItem();

        foreach ($recommendationItems as $recommendationItem) {
            $newRecommendationItem = clone $recommendationItemPrototype;

            if ($recommendationItem['links']) {
                $newRecommendationItem->clickRecommended = $recommendationItem['links']['clickRecommended'];
                $newRecommendationItem->rendered = $recommendationItem['links']['rendered'];
            }

            if ($recommendationItem['attributes']) {
                foreach ($recommendationItem['attributes'] as $attribute) {
                    if ($attribute['values']) {
                        $decodedHtmlString = html_entity_decode(strip_tags($attribute['values'][0]));
                        $newRecommendationItem->{$attribute['key']} = str_replace(['<![CDATA[', ']]>'], '', $decodedHtmlString);
                    }
                }
            }

            $newRecommendationItem->itemId = $recommendationItem['itemId'];
            $newRecommendationItem->itemType = $recommendationItem['itemType'];
            $newRecommendationItem->relevance = $recommendationItem['relevance'];

            $recommendationCollection[] = $newRecommendationItem;
        }

        unset($recommendationItemPrototype);

        return $recommendationCollection;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
     *
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getRecommendationMetadataParameters(ParameterBag $parameterBag): array
    {
        $contextItems = (int) $parameterBag->get(RecommendationMetadata::CONTEXT_ITEMS, 0);

        return [
            RecommendationMetadata::SCENARIO => $parameterBag->get(RecommendationMetadata::SCENARIO, ''),
            RecommendationMetadata::LIMIT => $parameterBag->get(RecommendationMetadata::LIMIT, 3),
            RecommendationMetadata::CONTEXT_ITEMS => $contextItems,
            RecommendationMetadata::CONTENT_TYPE => $this->contentHelper->getContentTypeId($this->contentHelper->getContentIdentifier($contextItems)),
            RecommendationMetadata::OUTPUT_TYPE_ID => $this->contentHelper->getContentTypeId($parameterBag->get(RecommendationMetadata::OUTPUT_TYPE_ID, '')),
            RecommendationMetadata::CATEGORY_PATH => $this->contentHelper->getLocationPathString($contextItems),
            RecommendationMetadata::LANGUAGE => $parameterBag->get($this->localeConverter->convertToEz(self::LOCALE_REQUEST_KEY), self::DEFAULT_LOCALE),
            RecommendationMetadata::ATTRIBUTES => $parameterBag->get(RecommendationMetadata::ATTRIBUTES, []),
            RecommendationMetadata::FILTERS => $parameterBag->get(RecommendationMetadata::FILTERS, []),
        ];
    }
}
