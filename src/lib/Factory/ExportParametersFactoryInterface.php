<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Factory;

use EzSystems\EzRecommendationClient\Value\ExportParameters;

interface ExportParametersFactoryInterface
{
    /**
     * @param array $properties
     *
     * @return \EzSystems\EzRecommendationClient\Value\ExportParameters
     */
    public function create(array $properties = []): ExportParameters;
}
