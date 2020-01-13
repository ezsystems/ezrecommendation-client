<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service;

use EzSystems\EzRecommendationClient\Value\ExportParameters;
use Symfony\Component\Console\Output\OutputInterface;

interface ExportServiceInterface
{
    public function process(ExportParameters $parameters, OutputInterface $output): void;
}
