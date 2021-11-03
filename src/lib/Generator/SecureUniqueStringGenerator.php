<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Generator;

use EzSystems\EzRecommendationClient\Exception\InvalidArgumentException;

final class SecureUniqueStringGenerator implements UniqueStringGeneratorInterface
{
    private const DEFAULT_ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    private string $alphabet;

    private int $alphabetLength;

    public function __construct(string $alphabet = self::DEFAULT_ALPHABET)
    {
        $this->alphabet = $alphabet;
        $this->alphabetLength = strlen($alphabet);
    }

    /**
     * @throws \EzSystems\EzRecommendationClient\Exception\InvalidArgumentException
     * @throws \Exception
     */
    public function generate(int $length): string
    {
        if ($length < 1) {
            throw new InvalidArgumentException('Length must be greater than 0');
        }

        $value = '';
        for ($i = 0; $i < $length; ++$i) {
            $value .= $this->alphabet[random_int(0, $this->alphabetLength)];
        }

        return $value;
    }
}
