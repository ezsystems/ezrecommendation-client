<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use Symfony\Component\HttpFoundation\ParameterBag;

class ParameterHelper
{
    /**
     * Returns parameters specified in $allowedParameters.
     *
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameters
     * @param array $allowedParameters
     *
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function parseParameters(ParameterBag $parameters, array $allowedParameters): ParameterBag
    {
        $parsedParameters = new ParameterBag();

        foreach ($allowedParameters as $parameter) {
            if ($parameters->has($parameter)) {
                $parsedParameters->set($parameter, $parameters->get($parameter));
            }
        }

        return $parsedParameters;
    }
}
