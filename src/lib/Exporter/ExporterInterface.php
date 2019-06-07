<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exporter;

use Symfony\Component\Console\Output\OutputInterface;

interface ExporterInterface
{
    /**
     * @param array $options
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function runExport(array $options, OutputInterface $output): void;
}
