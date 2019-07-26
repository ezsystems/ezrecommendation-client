<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service;

use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\SPI\RecommendationRequest;
use EzSystems\EzRecommendationClient\Value\RecommendationItem;
use Psr\Http\Message\ResponseInterface;

class RecommendationService implements RecommendationServiceInterface
{
    /** @var \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface */
    private $client;

    /** @param \EzSystems\EzRecommendationClient\Service\UserServiceInterface */
    private $userService;

    /**
     * @param \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface $client
     * @param \EzSystems\EzRecommendationClient\Service\UserServiceInterface $userService
     */
    public function __construct(
        EzRecommendationClientInterface $client,
        UserServiceInterface $userService
    ) {
        $this->client = $client;
        $this->userService = $userService;

        $this->client->setUserIdentifier($this->userService->getUserIdentifier());
    }

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getRecommendations(RecommendationRequest $request): ?ResponseInterface
    {
        return $this->client
            ->recommendation()
            ->getRecommendations($request);
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
}
