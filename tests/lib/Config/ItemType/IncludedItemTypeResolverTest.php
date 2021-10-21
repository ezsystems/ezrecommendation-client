<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\PersonalizationClient\Config\ItemType;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Ibexa\PersonalizationClient\Config\ItemType\IncludedItemTypeResolver;
use Ibexa\PersonalizationClient\Config\ItemType\IncludedItemTypeResolverInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Ibexa\PersonalizationClient\Config\ItemType\IncludedItemTypeResolver
 */
final class IncludedItemTypeResolverTest extends TestCase
{
    private const SITE_ACCESS_FOO = 'foo';
    private const SITE_ACCESS_BAR = 'bar';

    private const CONFIGURED_ITEM_TYPES_DEFAULT = ['d_foo', 'd_bar', 'd_baz'];
    private const CONFIGURED_ITEM_TYPES_SITE_ACCESS_FOO = ['f_foo', 'f_bar', 'f_baz'];
    private const CONFIGURED_ITEM_TYPES_SITE_ACCESS_BAR = ['b_foo', 'b_bar', 'b_baz'];

    private const SITE_ACCESS_MAP = [
        self::SITE_ACCESS_FOO => self::CONFIGURED_ITEM_TYPES_SITE_ACCESS_FOO,
        self::SITE_ACCESS_BAR => self::CONFIGURED_ITEM_TYPES_SITE_ACCESS_BAR,
    ];

    private IncludedItemTypeResolverInterface $itemTypeResolver;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private ConfigResolverInterface $configResolver;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->itemTypeResolver = new IncludedItemTypeResolver($this->configResolver);
        $this->itemTypeResolver->setLogger($this->logger);
    }

    /**
     * @dataProvider provideDataForTestResolve
     *
     * @param array<string> $expectedItemTypes
     * @param array<string> $inputItemTypes
     */
    public function testResolve(
        array $expectedItemTypes,
        array $inputItemTypes,
        bool $useLogger,
        ?string $siteAccess = null
    ): void {
        $this->configureConfigResolverToReturnIncludedContentTypes($siteAccess);

        if ($useLogger) {
            $this->configureLoggerToLogWarning($expectedItemTypes, $inputItemTypes);
        }

        self::assertEquals(
            $expectedItemTypes,
            $this->itemTypeResolver->resolve($inputItemTypes, $useLogger, $siteAccess)
        );
    }

    /**
     * @phpstan-return iterable<array{
     *  array<string>,
     *  array<string>,
     *  bool,
     *  3?: string
     * }>
     */
    public function provideDataForTestResolve(): iterable
    {
        yield [
            ['d_foo', 'd_bar', 'd_baz'],
            ['d_foo', 'd_bar', 'd_baz'],
            false,
        ];

        yield [
            ['f_foo', 'f_bar', 'f_baz'],
            ['f_foo', 'f_bar', 'f_baz'],
            false,
            self::SITE_ACCESS_FOO,
        ];

        yield [
            ['b_foo', 'b_bar', 'b_baz'],
            ['b_foo', 'b_bar', 'b_baz'],
            false,
            self::SITE_ACCESS_BAR,
        ];

        yield [
            ['d_foo'],
            ['d_foo'],
            false,
        ];

        yield [
            ['d_foo'],
            ['d_foo', 'test', 'folder'],
            true,
        ];

        yield [
            [],
            ['test', 'folder'],
            true,
        ];

        yield [
            [],
            ['foo', 'bar'],
            true,
        ];
    }

    private function configureConfigResolverToReturnIncludedContentTypes(?string $siteAccess = null): void
    {
        $configuredItemTypes = [];

        if (null === $siteAccess) {
            $configuredItemTypes = self::CONFIGURED_ITEM_TYPES_DEFAULT;
        }

        if (isset(self::SITE_ACCESS_MAP[$siteAccess])) {
            $configuredItemTypes = self::SITE_ACCESS_MAP[$siteAccess];
        }

        $this->configResolver
            ->expects(self::once())
            ->method('getParameter')
            ->with('included_content_types', 'ezrecommendation', $siteAccess)
            ->willReturn($configuredItemTypes);
    }

    /**
     * @param array<string> $expectedItemTypes
     * @param array<string> $inputItemTypes
     */
    private function configureLoggerToLogWarning(array $expectedItemTypes, array $inputItemTypes): void
    {
        $message = sprintf(
            'Item types: %s are not configured as included item types'
            . ' and have been removed from resolving criteria',
            implode(', ', array_diff($inputItemTypes, $expectedItemTypes))
        );

        $this->logger
            ->expects(self::once())
            ->method('warning')
            ->with(self::matches($message));
    }
}
