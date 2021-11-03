<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\PersonalizationClient\Generator;

use EzSystems\EzRecommendationClient\Exception\InvalidArgumentException;
use Ibexa\PersonalizationClient\Generator\SecureUniqueStringGenerator;
use Ibexa\PersonalizationClient\Generator\UniqueStringGeneratorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\PersonalizationClient\Generator\SecureUniqueStringGenerator
 */
final class SecureUniqueStringGeneratorTest extends TestCase
{
    private UniqueStringGeneratorInterface $uniqueStringGenerator;

    protected function setUp(): void
    {
        $this->uniqueStringGenerator = new SecureUniqueStringGenerator();
    }

    public function testThrowExceptionWhenLengthIsLessThan1(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Length must be greater than 0');

        $this->uniqueStringGenerator->generate(0);
    }

    /**
     * @throws \EzSystems\EzRecommendationClient\Exception\InvalidArgumentException
     */
    public function testGenerate(): void
    {
        self::assertEquals(
            20,
            strlen($this->uniqueStringGenerator->generate(20))
        );
    }
}
