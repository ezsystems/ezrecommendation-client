<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\File;

use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzRecommendationClient\Generator\ContentListElementGenerator;
use EzSystems\EzRecommendationClient\Value\ContentData;
use Psr\Log\LoggerInterface;

final class ExportFileGenerator
{
    /** @var \EzSystems\EzRecommendationClient\File\FileManagerInterface */
    private $fileManager;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \EzSystems\EzRecommendationClient\Generator\ContentListElementGenerator */
    private $contentListElementGenerator;

    /** @var \EzSystems\EzPlatformRest\Output\Generator */
    private $outputGenerator;

    public function __construct(
        FileManagerInterface $fileManager,
        LoggerInterface $logger,
        ContentListElementGenerator $contentListElementGenerator,
        Generator $outputGenerator
    ) {
        $this->fileManager = $fileManager;
        $this->logger = $logger;
        $this->contentListElementGenerator = $contentListElementGenerator;
        $this->outputGenerator = $outputGenerator;
    }

    public function generateFile(array $content, string $chunkPath, array $options): void
    {
        $data = new ContentData($content, $options);

        $this->outputGenerator->reset();
        $this->outputGenerator->startDocument($data);

        $this->generateFileContent($data);

        $filePath = $this->fileManager->getDir() . $chunkPath;
        $this->fileManager->save($filePath, $this->outputGenerator->endDocument($data));

        unset($data);

        $this->logger->info(sprintf('Generating file: %s', $filePath));
    }

    private function generateFileContent(ContentData $data): void
    {
        $contents = [];

        foreach ($data->contents as $contentTypes) {
            foreach ($contentTypes as $contentType) {
                $contents[] = $contentType;
            }
        }

        $this->contentListElementGenerator->generateElement($this->outputGenerator, $contents);

        unset($contents);
    }
}
