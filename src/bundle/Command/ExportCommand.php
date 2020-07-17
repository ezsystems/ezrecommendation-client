<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Command;

use EzSystems\EzRecommendationClient\Factory\ExportParametersFactoryInterface;
use EzSystems\EzRecommendationClient\Helper\ParamsConverterHelper;

use EzSystems\EzRecommendationClient\Http\HttpEnvironmentInterface;
use EzSystems\EzRecommendationClient\Service\ExportServiceInterface;
use EzSystems\EzRecommendationClient\Value\ExportParameters;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates and export content to Recommendation Server for a given command options.
 */
final class ExportCommand extends Command
{
    public const SUCCESS = 0;

    /** @var \EzSystems\EzRecommendationClient\Service\ExportServiceInterface */
    private $exportService;

    /** @var \EzSystems\EzRecommendationClient\Http\HttpEnvironmentInterface */
    private $httpEnvironment;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \EzSystems\EzRecommendationClient\Factory\ExportParametersFactoryInterface */
    private $exportParametersFactory;

    public function __construct(
        ExportServiceInterface $exportService,
        HttpEnvironmentInterface $httpEnvironment,
        LoggerInterface $logger,
        ExportParametersFactoryInterface $exportParametersFactory
    ) {
        parent::__construct();

        $this->exportService = $exportService;
        $this->httpEnvironment = $httpEnvironment;
        $this->logger = $logger;
        $this->exportParametersFactory = $exportParametersFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ezrecommendation:export:run')
            ->setDescription('Run export to files.')
            ->addOption('webHook', null, InputOption::VALUE_OPTIONAL, 'Guzzle Client base_uri parameter, will be used to send recommendation data')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Host used in exportDownload url for notifier in export feature')
            ->addOption('customerId', null, InputOption::VALUE_OPTIONAL, 'Your eZ Recommendation customer ID')
            ->addOption('licenseKey', null, InputOption::VALUE_OPTIONAL, 'Your eZ Recommendation license key')
            ->addOption('pageSize', null, InputOption::VALUE_OPTIONAL, '', 500)
            ->addOption('page', null, InputOption::VALUE_OPTIONAL, '', 1)
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'A string of subtree path, eg: /1/2/')
            ->addOption('hidden', null, InputOption::VALUE_OPTIONAL, 'If set to 1 - Criterion Visibility: VISIBLE will be used', 0)
            ->addOption('image', null, InputOption::VALUE_OPTIONAL, 'Image_variations used for images')
            ->addOption('contentTypeIdList', null, InputOption::VALUE_REQUIRED, 'List of Content Types ID')
            ->addOption('fields', null, InputOption::VALUE_OPTIONAL, 'List of the fields, eg: title, description')
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->httpEnvironment->prepare();

            date_default_timezone_set('UTC');

            $options = array_diff_key(
                $input->getOptions(),
                $this->getApplication()->getDefinition()->getOptions()
            );
            $options['siteaccess'] = $input->getOption('siteaccess');

            $this->exportService->process(
                $this->exportParametersFactory->create($options),
                $output
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }
}
