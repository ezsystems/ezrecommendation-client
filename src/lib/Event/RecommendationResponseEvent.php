<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Contracts\EventDispatcher\Event;

final class RecommendationResponseEvent extends Event
{
    /** @var \Psr\Http\Message\ResponseInterface|null */
    private $parameterBag;

    /** @var \EzSystems\EzRecommendationClient\Value\RecommendationItem[] */
    private $recommendationItems;

    public function __construct(ParameterBag $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function getParameterBag(): ParameterBag
    {
        return $this->parameterBag;
    }

    public function setRecommendationItems(array $recommendationItems)
    {
        $this->recommendationItems = $recommendationItems;
    }

    /**
     * @return \EzSystems\EzRecommendationClient\Value\RecommendationItem[]|null
     */
    public function getRecommendationItems(): ?array
    {
        return $this->recommendationItems;
    }
}
