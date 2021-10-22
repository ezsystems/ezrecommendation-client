<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Generator\Password;

final class RandomPasswordGenerator implements PasswordGeneratorInterface
{
    private const DEFAULT_LENGTH = 10;

    /**
     * @throws \Exception
     */
    public function generate(): string
    {
        $password = random_bytes(self::DEFAULT_LENGTH);

        return bin2hex($password);
    }
}
