<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;

class RecommendationResponseEvent extends Event
{
    const NAME = 'recommendation.response';

    /** @var \Psr\Http\Message\ResponseInterface|null */
    private $parameterBag;

    /** @var RecommendationItem[] */
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
     * @return RecommendationItem[]|null
     */
    public function getRecommendationItems(): ?array
    {
        return $this->recommendationItems;
    }
}
