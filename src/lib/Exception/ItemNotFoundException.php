<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exception;

use Throwable;

final class ItemNotFoundException extends NotFoundException
{
    public function __construct(?string $itemId = null, ?string $language = null, int $code = 0, Throwable $previous = null)
    {
        $message = 'Item not found';

        if (isset($itemId, $language)) {
            $message .= sprintf(' with id: %s and language: %s', $itemId, $language);
        }

        parent::__construct(
            $message,
            $code,
            $previous
        );
    }
}
