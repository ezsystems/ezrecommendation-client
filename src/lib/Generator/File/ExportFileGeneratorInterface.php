<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Generator\File;

use Ibexa\PersonalizationClient\Value\Export\FileSettings;

/**
 * @internal
 */
interface ExportFileGeneratorInterface
{
    public function generate(FileSettings $fileSettings): void;
}
