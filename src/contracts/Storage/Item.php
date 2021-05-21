<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\PersonalizationClient\Storage;

abstract class Item
{
    private string $id;

    private ItemTypeInterface $type;

    private string $language;

    /** @var array<string, string|int|array> */
    private array $attributes;

    /**
     * @param array<string, string|int|array> $attributes
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
     * @return array<string, string|int|array>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
