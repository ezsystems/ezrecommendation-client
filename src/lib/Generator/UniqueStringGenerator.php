<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Generator;

final class UniqueStringGenerator implements UniqueStringGeneratorInterface
{
    public function generate(int $length): string
    {
        return substr(md5(uniqid()), 0, $length);
    }
}
