<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\PersonalizationClient\Strategy\Credentials;

use Ibexa\PersonalizationClient\Generator\Password\PasswordGeneratorInterface;
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

    /** @var \Ibexa\PersonalizationClient\Generator\Password\PasswordGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private PasswordGeneratorInterface $passwordGenerator;

    protected function setUp(): void
    {
        $this->passwordGenerator = $this->createMock(PasswordGeneratorInterface::class);
        $this->credentialsStrategy = new BasicMethodStrategy($this->passwordGenerator);
    }

    public function testGetCredentials(): void
    {
        $this->passwordGenerator
            ->expects(self::once())
            ->method('generate')
            ->willReturn('1aqA3eTy89CzdwJkdwad');

        self::assertEquals(
            new Credentials('ibx', '1aqA3eTy89CzdwJkdwad'),
            $this->credentialsStrategy->getCredentials()
        );
    }
}
