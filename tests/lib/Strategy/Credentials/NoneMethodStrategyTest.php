<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\PersonalizationClient\Strategy\Credentials;

use Ibexa\PersonalizationClient\Strategy\Credentials\ExportCredentialsStrategyInterface;
use Ibexa\PersonalizationClient\Strategy\Credentials\NoneMethodStrategy;
use Ibexa\PersonalizationClient\Value\Export\Credentials;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\PersonalizationClient\Strategy\Credentials\NoneMethodStrategy
 */
final class NoneMethodStrategyTest extends TestCase
{
    private ExportCredentialsStrategyInterface $credentialsStrategy;

    protected function setUp(): void
    {
        $this->credentialsStrategy = new NoneMethodStrategy();
    }

    public function testGetCredentials(): void
    {
        self::assertEquals(
            new Credentials(),
            $this->credentialsStrategy->getCredentials()
        );
    }
}
