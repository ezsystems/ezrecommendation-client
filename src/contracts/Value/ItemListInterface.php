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
 * @extends IteratorAggregate<ItemInterface>
 */
interface ItemListInterface extends IteratorAggregate, Countable
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
}
