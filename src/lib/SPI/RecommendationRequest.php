<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\SPI;

/**
 * Class allow sends own Request object parameters to recommendation engine.
 */
abstract class RecommendationRequest extends Request
{
    const SCENARIO = 'scenario';

    /** @var string */
    public $scenario;
}
