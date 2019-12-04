<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service;

use eZ\Publish\Core\Repository\Values\Content\Content as CoreContent;
use EzSystems\EzRecommendationClient\SPI\Content;
use EzSystems\EzRecommendationClient\SPI\Content as ContentOptions;
use EzSystems\EzRecommendationClient\Value\ExportParameters;
use Symfony\Component\Console\Output\OutputInterface;

interface ContentServiceInterface
{
    /**
     * @param int $contentTypeId
     * @param \EzSystems\EzRecommendationClient\Value\ExportParameters $parameters
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return array
     */
    public function fetchContent(int $contentTypeId, ExportParameters $parameters, OutputInterface $output): array;

    /**
     * Prepare content array.
     *
     * @param int $contentTypeId
     * @param \EzSystems\EzRecommendationClient\Value\ExportParameters $parameters
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return array
     */
    public function fetchContentItems(int $contentTypeId, ExportParameters $parameters, OutputInterface $output): array;

    /**
     * @param array $data
     * @param \EzSystems\EzRecommendationClient\SPI\Content $options
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     * @return array
     */
    public function prepareContent(array $data, Content $options, ?OutputInterface $output = null): array;


    /**
     * @param \eZ\Publish\Core\Repository\Values\Content\Content $content
     * @param \EzSystems\EzRecommendationClient\SPI\Content $options
     *
     * @return array
     */
    public function setContent(CoreContent $content, ContentOptions $options): array;
}
