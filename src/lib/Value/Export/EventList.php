<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Value\Export;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use eZ\Publish\API\Repository\Exceptions\OutOfBoundsException;
use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @implements IteratorAggregate<array-key, \Ibexa\PersonalizationClient\Value\Export\Event>
 * @implements ArrayAccess<array-key, \Ibexa\PersonalizationClient\Value\Export\Event>
 */
final class EventList implements IteratorAggregate, ArrayAccess
{
    /** @var array<\Ibexa\PersonalizationClient\Value\Export\Event> */
    private array $eventList;

    /**
     * @param array<\Ibexa\PersonalizationClient\Value\Export\Event> $eventList
     */
    public function __construct(array $eventList)
    {
        Assert::allIsInstanceOf($eventList, Event::class);

        $this->eventList = $eventList;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->eventList);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->eventList[$offset]);
    }

    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException(
            'Unsupported method'
        );
    }

    public function offsetGet($offset): Event
    {
        if (false === $this->offsetExists($offset)) {
            throw new OutOfBoundsException(
                sprintf('The collection does not contain an element with index: %d', $offset)
            );
        }

        return $this->eventList[$offset];
    }

    public function offsetUnset($offset): void
    {
        unset($this->eventList[$offset]);
    }

    public function countItemTypes(): int
    {
        $types = [];
        foreach ($this->eventList as $event) {
            $types[] = $event->getItemTypeName();
        }

        return count(array_unique($types));
    }
}
