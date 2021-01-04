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
use Symfony\Component\Process\ProcessBuilder;

/**
 * Runs export command as separate process.
 */
class ExportProcessRunnerHelper
{
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var string */
    private $phpPath;

    /** @var string */
    private $kernelEnvironment;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $kernelEnvironment
     */
    public function __construct(
        LoggerInterface $logger,
        string $kernelEnvironment
    ) {
        $this->logger = $logger;
        $this->kernelEnvironment = $kernelEnvironment;
    }

    /**
     * @param array $parameters
     */
    public function run(array $parameters): void
    {
        $documentRoot = $parameters['documentRoot'];
        unset($parameters['documentRoot']);

        $builder = new ProcessBuilder([
            $documentRoot . '/../bin/console',
            'ezrecomendation:export:run',
            '--env=' . $this->kernelEnvironment,
        ]);
        $builder->setWorkingDirectory($documentRoot . '../');
        $builder->setTimeout(null);
        $builder->setPrefix([
            $this->getPhpPath(),
            '-d',
            'memory_limit=-1',
        ]);

        foreach ($parameters as $key => $option) {
            if (empty($option)) {
                continue;
            }

            if (is_array($option)) {
                $option = implode(',', $option);
            }

            $builder->add(sprintf('--%s=%s', $key, $option));
        }

        $command = $builder->getProcess()->getCommandLine();
        $output = sprintf(
            ' > %s 2>&1 & echo $! > %s',
            $documentRoot . '/var/export/.log',
            $documentRoot . '/var/export/.pid'
        );

        $this->logger->info(sprintf('Running command: %s', $command . $output));

        $process = new Process($command . $output);
        $process->disableOutput();
        $process->run();
    }

    /**
     * @return string
     */
    private function getPhpPath(): string
    {
        if ($this->phpPath) {
            return $this->phpPath;
        }

        $phpFinder = new PhpExecutableFinder();
        $this->phpPath = $phpFinder->find();
        if (!$this->phpPath) {
            throw new \RuntimeException(
                'The php executable could not be found, it\'s needed for executing parable sub processes, so add it to your PATH environment variable and try again'
            );
        }

        return $this->phpPath;
    }
}
