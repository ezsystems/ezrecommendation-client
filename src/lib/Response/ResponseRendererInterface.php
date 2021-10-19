<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Response;

use EzSystems\EzPlatformRest\Output\Generator;
use Ibexa\Contracts\PersonalizationClient\Value\ItemListInterface;

/**
 * @internal
 */
interface ResponseRendererInterface
{
    public function render(Generator $generator, ItemListInterface $itemList): Generator;
}
