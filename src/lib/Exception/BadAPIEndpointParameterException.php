<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exception;

class BadAPIEndpointParameterException extends APIException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Bad API endpoint parameter given.', 0, $previous);
    }
}
