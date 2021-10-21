<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Config\ItemType;

interface IncludedItemTypeResolverInterface
{
    /**
     * @param array<string> $inputItemTypes
     *
     * @return array<string>
     */
    public function resolve(array $inputItemTypes, bool $useLogger, ?string $siteAccess = null): array;
}
