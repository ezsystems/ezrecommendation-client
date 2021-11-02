<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\PersonalizationClient\Strategy\Credentials;

use Ibexa\PersonalizationClient\Generator\UniqueStringGeneratorInterface;
use Ibexa\PersonalizationClient\Strategy\Credentials\BasicMethodStrategy;
use Ibexa\PersonalizationClient\Strategy\Credentials\ExportCredentialsStrategyInterface;
use Ibexa\PersonalizationClient\Value\Export\Credentials;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\PersonalizationClient\Strategy\Credentials\BasicMethodStrategy
 */
final class BasicMethodStrategyTest extends TestCase
{
    private ExportCredentialsStrategyInterface $credentialsStrategy;

    /** @var \Ibexa\PersonalizationClient\Generator\UniqueStringGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private UniqueStringGeneratorInterface $uniqueStringGenerator;

    protected function setUp(): void
    {
        $this->uniqueStringGenerator = $this->createMock(UniqueStringGeneratorInterface::class);
        $this->credentialsStrategy = new BasicMethodStrategy($this->uniqueStringGenerator);
    }

    public function testGetCredentials(): void
    {
        $this->uniqueStringGenerator
            ->expects(self::atLeastOnce())
            ->method('generate')
            ->withConsecutive(
                [10],
                [30],
            )
            ->willReturnOnConsecutiveCalls('1f3b03cd5', '1aqA3eTy89CzdwJkdwad');

        self::assertEquals(
            new Credentials('1f3b03cd5', '1aqA3eTy89CzdwJkdwad'),
            $this->credentialsStrategy->getCredentials()
        );
    }
}
