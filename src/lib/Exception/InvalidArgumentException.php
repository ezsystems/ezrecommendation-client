<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exception;

use Exception;

/**
 * Generates InvalidArgumentException with customised message.
 *
 * @param \Exception|null $previous
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    public function __construct($message, ?Exception $previous)
    {
        parent::__construct(
            $message, 0, $previous
        );
    }
}
