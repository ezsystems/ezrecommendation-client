<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Personalization\Value;

use Closure;
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

    public function filter(Closure $predicate): self;
}
