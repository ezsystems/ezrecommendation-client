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

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
     */
    public function __construct(ParameterBag $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getParameterBag(): ParameterBag
    {
        return $this->parameterBag;
    }

    /**
     * @param array $recommendationItems
     */
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
