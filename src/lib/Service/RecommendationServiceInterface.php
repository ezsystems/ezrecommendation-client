<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service;

use EzSystems\EzRecommendationClient\SPI\RecommendationRequest;
use EzSystems\EzRecommendationClient\Value\RecommendationItem;
use Psr\Http\Message\ResponseInterface;

interface RecommendationServiceInterface
{
    /**
     * @param \EzSystems\EzRecommendationClient\SPI\RecommendationRequest $request
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function getRecommendations(RecommendationRequest $request): ?ResponseInterface;

    /**
     * @param string $outputContentType
     */
    public function sendDeliveryFeedback(string $outputContentType): void;

    /**
     * @param array $recommendationItems
     *
     * @return RecommendationItem[]
     */
    public function getRecommendationItems(array $recommendationItems): array;
}
