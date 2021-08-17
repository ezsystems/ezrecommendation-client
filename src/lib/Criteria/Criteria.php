<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Personalization\Criteria;

use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;

final class Criteria implements CriteriaInterface
{
    public const LIMIT = 50;

    /** @var array<string> */
    private array $itemTypeIdentifiers;

    /** @var array<string> */
    private array $languages;

    private int $limit;

    private int $offset;

    /**
     * @param array<string> $itemTypeIdentifiers
     * @param array<string> $languages
     */
    public function __construct(
        array $itemTypeIdentifiers,
        array $languages,
        int $limit = self::LIMIT,
        int $offset = 0
    ) {
        $this->itemTypeIdentifiers = $itemTypeIdentifiers;
        $this->languages = $languages;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function getItemTypeIdentifiers(): array
    {
        return $this->itemTypeIdentifiers;
    }

    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}
