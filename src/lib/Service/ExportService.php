<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service;

use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use EzSystems\EzRecommendationClient\Exception\InvalidArgumentException;
use EzSystems\EzRecommendationClient\Exporter\ExporterInterface;
use EzSystems\EzRecommendationClient\File\FileManagerInterface;
use EzSystems\EzRecommendationClient\Value\ExportParameters;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ExportService implements ExportServiceInterface
{
    /** @var \EzSystems\EzRecommendationClient\Exporter\ExporterInterface */
    private $exporter;

    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    private $validator;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface */
    private $credentialsResolver;

    /** @var \EzSystems\EzRecommendationClient\File\FileManagerInterface */
    private $fileManager;

    /** @var \EzSystems\EzRecommendationClient\Service\ExportNotificationService */
    private $notificationService;

    public function __construct(
        ExporterInterface $exporter,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        CredentialsResolverInterface $credentialsResolver,
        FileManagerInterface $fileManager,
        NotificationService $notificationService
    ) {
        $this->exporter = $exporter;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->credentialsResolver = $credentialsResolver;
        $this->fileManager = $fileManager;
        $this->notificationService = $notificationService;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function process(ExportParameters $parameters, OutputInterface $output): void
    {
        $errors = $this->validator->validate($parameters);

        if (\count($errors) > 0) {
            $errors = (string)$errors;

            $output->write($errors);
            $this->logger->error($errors);

            throw new InvalidArgumentException($errors);
        }

        $this->runExport($parameters, $output);
    }

    /**
     * @throws \Exception
     */
    private function runExport(ExportParameters $parameters, OutputInterface $output): void
    {
        try {
            $chunkDir = $this->fileManager->createChunkDir();
            $this->fileManager->lock();
            $exportFiles = $this->exporter->run($parameters, $chunkDir, $output);
            $this->fileManager->unlock();

            $response = $this->notificationService->sendNotification(
                $parameters,
                $exportFiles,
                $this->getSecuredDirCredentials($chunkDir)
            );

            $this->logger->info(sprintf('eZ Recommendation Response: %s', $response->getBody()));
            $output->writeln('Done');
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Error while generating export: %s', $e->getMessage()));
            $this->fileManager->unlock();

            throw $e;
        }
    }

    /**
     * @return string[]
     */
    private function getSecuredDirCredentials(string $chunkDir): array
    {
        /** @var \EzSystems\EzRecommendationClient\Value\Config\ExportCredentials $credentials */
        $credentials = $this->credentialsResolver->getCredentials();

        return $this->fileManager->secureDir($chunkDir, $credentials);
    }
}
