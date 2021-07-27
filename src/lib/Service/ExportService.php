<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service;

use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use EzSystems\EzRecommendationClient\Exporter\ExporterInterface;
use EzSystems\EzRecommendationClient\File\FileManagerInterface;
use Ibexa\Personalization\Value\Export\Parameters;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ExportService implements ExportServiceInterface
{
    private ExporterInterface $exporter;

    /** @var \Psr\Log\LoggerInterface */
    private LoggerInterface $logger;

    private CredentialsResolverInterface $credentialsResolver;

    private FileManagerInterface $fileManager;

    /** @var \EzSystems\EzRecommendationClient\Service\ExportNotificationService */
    private ExportNotificationService $notificationService;

    public function __construct(
        ExporterInterface $exporter,
        LoggerInterface $logger,
        CredentialsResolverInterface $credentialsResolver,
        FileManagerInterface $fileManager,
        NotificationService $notificationService
    ) {
        $this->exporter = $exporter;
        $this->logger = $logger;
        $this->credentialsResolver = $credentialsResolver;
        $this->fileManager = $fileManager;
        $this->notificationService = $notificationService;
    }

    public function runExport(Parameters $parameters, OutputInterface $output): void
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

            if ($response) {
                $this->logger->info(sprintf('eZ Recommendation Response: %s', $response->getBody()));
                $output->writeln('Done');
            }
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
