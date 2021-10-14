<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Value\Storage;

use ArrayIterator;
use Ibexa\Contracts\PersonalizationClient\Value\ItemGroupListInterface;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<\Ibexa\Contracts\PersonalizationClient\Value\ItemGroupInterface>
 */
final class ItemGroupList implements IteratorAggregate, ItemGroupListInterface
{
    /** @var array<\Ibexa\Contracts\PersonalizationClient\Value\ItemGroupInterface> */
    private array $groups;

    /**
     * @param array<\Ibexa\Contracts\PersonalizationClient\Value\ItemGroupInterface> $groups
     */
    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }

    public function getGroups(): iterable
    {
        return $this->groups;
    }

    public function count(): int
    {
        return count($this->groups);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->groups);
    }
}
