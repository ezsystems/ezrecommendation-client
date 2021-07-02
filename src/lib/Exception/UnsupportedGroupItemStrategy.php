<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exception;

use RuntimeException;
use Throwable;

final class UnsupportedGroupItemStrategy extends RuntimeException implements EzRecommendationException
{
    public function __construct(string $strategy, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Unsupported GroupItemStrategy %s ', $strategy),
            $code,
            $previous
        );
    }
}
