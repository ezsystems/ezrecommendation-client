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
    public function getRecommendations(RecommendationRequest $request): ?ResponseInterface;

    public function sendDeliveryFeedback(string $outputContentType): void;

    /**
     * @return RecommendationItem[]
     */
    public function getRecommendationItems(array $recommendationItems): array;
}
