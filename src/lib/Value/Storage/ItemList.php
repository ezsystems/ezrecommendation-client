<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Personalization\Value\Storage;

use ArrayIterator;
use Closure;
use eZ\Publish\API\Repository\Exceptions\OutOfBoundsException;
use EzSystems\EzRecommendationClient\Exception\ItemNotFoundException;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemListInterface;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<\Ibexa\Contracts\Personalization\Value\ItemInterface>
 */
final class ItemList implements IteratorAggregate, ItemListInterface
{
    /** @var array<\Ibexa\Contracts\Personalization\Value\ItemInterface> */
    private array $items;

    /**
     * @param array<\Ibexa\Contracts\Personalization\Value\ItemInterface> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function filter(callable $predicate): ItemListInterface
    {
        return new self(
            array_values(
                array_filter(
                    $this->items,
                    $predicate
                )
            )
        );
    }

    public function get(string $identifier, string $language): ItemInterface
    {
        if (!$this->has($identifier, $language)) {
            throw new ItemNotFoundException($identifier, $language);
        }

        $items = array_filter($this->items, $this->getItemPredicate($identifier, $language));
        $item = current($items);

        if (!$item instanceof ItemInterface) {
            throw new ItemNotFoundException($identifier, $language);
        }

        return $item;
    }

    public function has(string $identifier, string $language): bool
    {
        return array_filter($this->items, $this->getItemPredicate($identifier, $language)) !== null;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function slice(int $offset, ?int $length = null): ItemListInterface
    {
        return new self(array_slice($this->items, $offset, $length));
    }

    public function first(): ItemInterface
    {
        if (empty($this->items)) {
            throw new OutOfBoundsException('Collection is empty');
        }

        return reset($this->items);
    }

    /**
     * @param \Traversable<\Ibexa\Contracts\Personalization\Value\ItemInterface> $traversable
     */
    public static function fromTraversable(Traversable $traversable): ItemListInterface
    {
        return new self(iterator_to_array($traversable));
    }

    private function getItemPredicate(string $identifier, string $language): Closure
    {
        return static function (ItemInterface $item) use ($identifier, $language): bool {
            return
                $item->getId() === $identifier
                && $item->getLanguage() === $language;
        };
    }
}
