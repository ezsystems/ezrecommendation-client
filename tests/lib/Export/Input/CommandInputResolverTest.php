<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Export\Input;

use Ibexa\Personalization\Export\Input\CommandInputResolver;
use Ibexa\Personalization\Export\Input\CommandInputResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class CommandInputResolverTest extends TestCase
{
    private CommandInputResolverInterface $inputResolver;

    /** @var \Symfony\Component\Console\Input\InputInterface|\PHPUnit\Framework\MockObject\MockObject */
    private InputInterface $input;

    /** @var \Symfony\Component\Console\Application|\PHPUnit\Framework\MockObject\MockObject */
    private Application $application;

    public function setUp(): void
    {
        $this->inputResolver = new CommandInputResolver();
        $this->input = $this->createMock(InputInterface::class);
        $this->application = $this->createMock(Application::class);
    }

    public function testResolve(): void
    {
        $this->input
            ->expects(self::once())
            ->method('getOptions')
            ->willReturn(
                [
                    'customer-id' => 1234,
                    'item-type-id' => 'product',
                    'host' => null,
                    'help' => false,
                    'version' => false,
                ]
            );

        $this->application
            ->expects(self::once())
            ->method('getDefinition')
            ->willReturn(
                new InputDefinition(
                    [
                        new InputArgument('command'),
                        new InputOption('help'),
                        new InputOption('version'),
                    ]
                )
            );

        self::assertEquals(
            [
                'customer_id' => 1234,
                'item_type_id' => 'product',
                'host' => null,
            ],
            $this->inputResolver->resolve($this->input, $this->application)
        );
    }
}
