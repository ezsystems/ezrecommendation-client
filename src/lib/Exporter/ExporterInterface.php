<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exporter;

use Ibexa\Personalization\Value\Export\Parameters;
use Symfony\Component\Console\Output\OutputInterface;

interface ExporterInterface
{
    public function run(Parameters $parameters, string $chunkDir, OutputInterface $output): array;
}
