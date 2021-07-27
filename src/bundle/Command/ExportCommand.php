<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Personalization\Command;

use eZ\Bundle\EzPublishCoreBundle\Command\BackwardCompatibleCommand;
use EzSystems\EzRecommendationClient\Http\HttpEnvironmentInterface;
use EzSystems\EzRecommendationClient\Service\ExportServiceInterface;
use Ibexa\Personalization\Export\Input\CommandInputResolverInterface;
use Ibexa\Personalization\Factory\Export\ParametersFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates and export content to Recommendation Server for a given command options.
 */
final class ExportCommand extends Command implements BackwardCompatibleCommand
{
    public const SUCCESS = 0;

    private ExportServiceInterface $exportService;

    private HttpEnvironmentInterface $httpEnvironment;

    private LoggerInterface $logger;

    private CommandInputResolverInterface $inputResolver;

    private ParametersFactoryInterface $exportParametersFactory;

    public function __construct(
        ExportServiceInterface $exportService,
        HttpEnvironmentInterface $httpEnvironment,
        LoggerInterface $logger,
        CommandInputResolverInterface $inputResolver,
        ParametersFactoryInterface $exportParametersFactory
    ) {
        parent::__construct();

        $this->exportService = $exportService;
        $this->httpEnvironment = $httpEnvironment;
        $this->logger = $logger;
        $this->inputResolver = $inputResolver;
        $this->exportParametersFactory = $exportParametersFactory;
    }

    protected function configure(): void
    {
        $this
            ->setName('ibexa:recommendation:run-export')
            ->setAliases(['ezrecommendation:export:run'])
            ->setDescription('Run export to files.')
            ->addOption('customer-id', null, InputOption::VALUE_REQUIRED, 'Personalization customer id')
            ->addOption('license-key', null, InputOption::VALUE_REQUIRED, 'Personalization license key')
            ->addOption('web-hook', null, InputOption::VALUE_OPTIONAL, 'Recommendation engine URI used to send recommendation data')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Host used in exportDownload url for notifier in export feature')
            ->addOption('item-type-identifier-list', null, InputOption::VALUE_REQUIRED, 'List of item types identifiers')
            ->addOption('languages', null, InputOption::VALUE_REQUIRED, 'List of items languages')
            ->addOption('page-size', null, InputOption::VALUE_OPTIONAL, '', '500');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->httpEnvironment->prepare();

            date_default_timezone_set('UTC');

            $this->exportService->runExport(
                $this->exportParametersFactory->create(
                    $this->inputResolver->resolve(
                        $input, $this->getApplication()
                    ),
                    ParametersFactoryInterface::COMMAND_TYPE
                ),
                $output
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    /**
     * @return string[]
     */
    public function getDeprecatedAliases(): array
    {
        return ['ezrecommendation:export:run'];
    }
}
