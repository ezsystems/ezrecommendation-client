<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Field;

use eZ\Publish\API\Repository\Values\Content\Content;

interface ContentFieldResolverInterface
{
    /**
     * @return array<string, string|float|array>
     */
    public function resolve(Content $content): array;
}
