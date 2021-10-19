<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Generator\ItemList;

use EzSystems\EzPlatformRest\Output\Generator;
use Ibexa\Contracts\PersonalizationClient\Value\ItemListInterface;

/**
 * @internal
 */
interface ItemListElementGeneratorInterface
{
    public function generateElement(Generator $generator, ItemListInterface $itemList): Generator;
}
