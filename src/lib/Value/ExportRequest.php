<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value;

class ExportRequest extends ExportParameters
{
    /** @var string */
    public $documentRoot;

    public function getExportRequestParameters(): array
    {
        return get_object_vars($this);
    }
}
