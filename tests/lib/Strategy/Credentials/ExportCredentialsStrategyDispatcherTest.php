<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\PersonalizationClient\Strategy\Credentials;

use Ibexa\PersonalizationClient\Exception\UnsupportedExportCredentialsMethodStrategy;
use Ibexa\PersonalizationClient\Strategy\Credentials\ExportCredentialsStrategyDispatcher;
use Ibexa\PersonalizationClient\Strategy\Credentials\ExportCredentialsStrategyDispatcherInterface;
use Ibexa\PersonalizationClient\Strategy\Credentials\ExportCredentialsStrategyInterface;
use Ibexa\PersonalizationClient\Value\Export\Credentials;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\PersonalizationClient\Strategy\Credentials\ExportCredentialsStrategyDispatcher
 */
final class ExportCredentialsStrategyDispatcherTest extends TestCase
{
    private ExportCredentialsStrategyDispatcherInterface $credentialsStrategyDispatcher;

    /** @var \Ibexa\PersonalizationClient\Strategy\Credentials\ExportCredentialsStrategyInterface|\PHPUnit\Framework\MockObject\MockObject */
    private ExportCredentialsStrategyInterface $strategyMethod;

    protected function setUp(): void
    {
        $this->strategyMethod = $this->createMock(ExportCredentialsStrategyInterface::class);

        $strategies = [
            'basic' => $this->strategyMethod,
            'user' => $this->strategyMethod,
            'none' => $this->strategyMethod,
        ];

        $this->credentialsStrategyDispatcher = new ExportCredentialsStrategyDispatcher($strategies);
    }

    /**
     * @dataProvider provideDataForTestGetCredentials
     */
    public function testGetCredentials(
        Credentials $expectedCredentials,
        string $method,
        ?string $siteAccess = null
    ): void {
        $this->strategyMethod
            ->expects(self::once())
            ->method('getCredentials')
            ->with($siteAccess)
            ->willReturn($expectedCredentials);

        self::assertEquals(
            $expectedCredentials,
            $this->credentialsStrategyDispatcher->getCredentials($method, $siteAccess)
        );
    }

    public function testThrowUnsupportedExportCredentialsMethodStrategy(): void
    {
        $this->expectException(UnsupportedExportCredentialsMethodStrategy::class);
        $this->expectExceptionMessage('Unsupported ExportCredentialsStrategy: test. Supported strategies: basic, user, none');

        $this->credentialsStrategyDispatcher->getCredentials('test');
    }

    /**
     * @phpstan-return iterable<array{
     *  \Ibexa\PersonalizationClient\Value\Export\Credentials,
     *  string,
     *  2?: string
     * }>
     */
    public function provideDataForTestGetCredentials(): iterable
    {
        yield [
            new Credentials(), 'none',
        ];

        yield [
            new Credentials('user_login', 'user_password'), 'user', 'site',
        ];

        yield [
            new Credentials('ibexa', '12344556566'), 'basic',
        ];
    }
}
