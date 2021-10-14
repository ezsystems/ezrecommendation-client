<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\PersonalizationClient\Command;

use Ibexa\Tests\Integration\PersonalizationClient\IbexaKernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractCommandTestCase extends IbexaKernelTestCase
{
    protected Command $command;

    protected CommandTester $commandTester;

    protected function setUp(): void
    {
        static::bootKernel();
        $application = new Application(self::$kernel);
        $this->command = $application->find(static::getCommandName());
        $this->commandTester = new CommandTester($this->command);
    }

    abstract protected static function getCommandName(): string;
}
