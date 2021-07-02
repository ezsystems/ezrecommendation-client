<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Stubs;

use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Contracts\Personalization\Value\ItemTypeInterface;

final class Item implements ItemInterface
{
    public const ITEM_BODY = 'body';
    public const ITEM_IMAGE = 'public/var/1/2/4/5/%s/%s';

    private string $id;

    private ItemTypeInterface $type;

    private string $language;

    /** @var array<string, int|string|array> */
    private array $attributes;

    /**
     * @param array<string, int|string|array> $attributes
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

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
