<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Personalization\Value;

use Countable;
use IteratorAggregate;

/**
 * @extends IteratorAggregate<ItemGroupInterface>
 */
interface ItemGroupListInterface extends IteratorAggregate, Countable
{
    /**
     * @return iterable<\Ibexa\Contracts\Personalization\Value\ItemGroupInterface>
     */
    public function getGroups(): iterable;
}
