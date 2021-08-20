<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Personalization\Value;

use Countable;
use Traversable;

/**
 * @extends Traversable<\Ibexa\Contracts\Personalization\Value\ItemInterface>
 */
interface ItemListInterface extends Traversable, Countable
{
    /**
     * @throws \EzSystems\EzRecommendationClient\Exception\ItemNotFoundException
     */
    public function get(string $identifier, string $language): ItemInterface;

    public function has(string $identifier, string $language): bool;

    /**
     * Returns a new ItemInterface collection containing matched elements.
     *
     * @phpstan-param callable(\Ibexa\Contracts\Personalization\Value\ItemInterface=): bool $predicate
     */
    public function filter(callable $predicate): self;

    /**
     * Returns a new ItemInterface collection sliced of $length elements starting at position $offset.
     */
    public function slice(int $offset, ?int $length = null): self;

    /**
     * @throws \EzSystems\EzRecommendationClient\Exception\ItemNotFoundException
     */
    public function first(): ItemInterface;
}
