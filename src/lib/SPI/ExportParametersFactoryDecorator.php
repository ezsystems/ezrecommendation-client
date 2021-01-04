<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\SPI;

use EzSystems\EzRecommendationClient\Factory\ExportParametersFactoryInterface;
use EzSystems\EzRecommendationClient\Value\ExportParameters;

abstract class ExportParametersFactoryDecorator implements ExportParametersFactoryInterface
{
    /** @var \EzSystems\EzRecommendationClient\Factory\ExportParametersFactoryInterface */
    protected $innerService;

    public function __construct(ExportParametersFactoryInterface $innerService)
    {
        $this->innerService = $innerService;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $properties = []): ExportParameters
    {
        return $this->innerService->create($properties);
    }
}
