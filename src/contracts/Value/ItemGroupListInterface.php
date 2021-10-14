<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\PersonalizationClient\Value;

use Countable;
use Traversable;

/**
 * @extends Traversable<\Ibexa\Contracts\PersonalizationClient\Value\ItemGroupInterface>
 */
interface ItemGroupListInterface extends Traversable, Countable
{
    /**
     * @return iterable<\Ibexa\Contracts\PersonalizationClient\Value\ItemGroupInterface>
     */
    public function getGroups(): iterable;
}
