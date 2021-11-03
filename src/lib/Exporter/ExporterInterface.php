<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exporter;

use Ibexa\PersonalizationClient\Value\Export\Parameters;

interface ExporterInterface
{
    public function export(Parameters $parameters): void;

    /**
     * @return iterable<\Ibexa\PersonalizationClient\Value\Export\Event>
     */
    public function getExportEvents(Parameters $parameters): iterable;

    public function hasExportItems(Parameters $parameters): bool;
}
