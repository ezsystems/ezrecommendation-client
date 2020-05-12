<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exception;

class CredentialsNotFoundException extends NotFoundException
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Credentials for recommendation client are not set', $code, $previous);
    }
}
