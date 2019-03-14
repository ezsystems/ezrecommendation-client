<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use InvalidArgumentException;

/**
 * Provides utility to manipulate strings.
 */
class ParamsConverterHelper
{
    /**
     * Preparing array of integers based on comma separated integers in string or single integer in string.
     *
     * @param string $string list of integers separated by comma character
     *
     * @return array
     *
     * @throws InvalidArgumentException If incorrect $list value is given
     */
    public static function getIdListFromString(string $string): array
    {
        if (filter_var($string, FILTER_VALIDATE_INT) !== false) {
            return [$string];
        }

        return array_map(
            function ($id) {
                if (false === filter_var($id, FILTER_VALIDATE_INT)) {
                    throw new InvalidArgumentException('String should be a list of Integers');
                }

                return (int) $id;
            },
            explode(',', $string)
        );
    }

    /**
     * Returns list of elements as array from comma separated string.
     *
     * @param string $string
     *
     * @return array
     */
    public static function getArrayFromString(string $string): array
    {
        if (strpos($string, ',') !== false) {
            return explode(',', $string);
        }

        return [$string];
    }
}
