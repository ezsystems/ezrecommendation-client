<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exception;

use Throwable;

final class CredentialsNotFoundException extends NotFoundException
{
    public function __construct(?string $siteAccess = null, int $code = 0, Throwable $previous = null)
    {
        $message = 'Credentials for recommendation client are not set';

        if (null !== $siteAccess) {
            $message .= ' for siteAccess: ' .  $siteAccess;
        }

        parent::__construct(
            $message,
            $code,
            $previous
        );
    }
}
