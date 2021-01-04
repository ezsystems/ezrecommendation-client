<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Runs export command as separate process.
 */
final class ExportProcessRunnerHelper
{
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var string */
    private $phpPath;

    /** @var string */
    private $kernelEnvironment;

    public function __construct(
        LoggerInterface $logger,
        string $kernelEnvironment
    ) {
        $this->logger = $logger;
        $this->kernelEnvironment = $kernelEnvironment;
    }

    public function run(array $parameters): Process
    {
        $documentRoot = $parameters['documentRoot'];
        unset($parameters['documentRoot']);

        $command = [
            $this->getPhpPath(),
            '-d',
            'memory_limit=-1',
            $documentRoot . '/../bin/console',
            'ezrecommendation:export:run',
            '--env=' . $this->kernelEnvironment,
        ];

        foreach ($parameters as $key => $option) {
            if (empty($option)) {
                continue;
            }

            if (\is_array($option)) {
                $option = implode(',', $option);
            }

            $command[] = sprintf('--%s=%s', $key, $option);
        }

        $process = new Process($command);

        $this->logger->info(sprintf('Running command: %s', $process->getCommandLine()));

        $process
            ->setTimeout(null)
            ->run()
        ;

        return $process;
    }

    private function getPhpPath(): string
    {
        if ($this->phpPath) {
            return $this->phpPath;
        }

        $phpFinder = new PhpExecutableFinder();
        $this->phpPath = $phpFinder->find();
        if (!$this->phpPath) {
            throw new \RuntimeException('The php executable could not be found, it\'s needed for executing parable sub processes, so add it to your PATH environment variable and try again');
        }

        return $this->phpPath;
    }
}
