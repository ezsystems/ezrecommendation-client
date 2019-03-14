<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exception;

use Throwable;

/**
 * Generates BadApiCallException.
 */
class BadApiCallException extends \BadFunctionCallException
{
    public function __construct($name, ?Throwable $previous)
    {
        $message = sprintf('Given API class %s is not callable', $name);

        parent::__construct($message, 0, $previous);
    }
}
