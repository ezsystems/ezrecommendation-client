<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Value\Storage;

use Ibexa\Contracts\PersonalizationClient\Value\ItemInterface;
use Ibexa\Contracts\PersonalizationClient\Value\ItemTypeInterface;

final class Item implements ItemInterface
{
    private string $id;

    private ItemTypeInterface $type;

    private string $language;

    /** @var array<string, string|float|array> */
    private array $attributes;

    /**
     * @param array<string, string|float|array> $attributes
     */
    public function __construct(
        string $id,
        ItemTypeInterface $type,
        string $language,
        array $attributes
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->language = $language;
        $this->attributes = $attributes;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): ItemTypeInterface
    {
        return $this->type;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return array<string, string|float|array>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
