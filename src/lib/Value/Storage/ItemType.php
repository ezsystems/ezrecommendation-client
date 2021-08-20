<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Personalization\Value\Storage;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Personalization\Value\ItemTypeInterface;

final class ItemType implements ItemTypeInterface
{
    private string $identifier;

    private int $id;

    private string $name;

    public function __construct(
        string $identifier,
        int $id,
        string $name
    ) {
        $this->identifier = $identifier;
        $this->id = $id;
        $this->name = $name;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function fromContentType(ContentType $contentType): self
    {
        return new self(
            $contentType->identifier,
            $contentType->id,
            $contentType->getName() ?? $contentType->identifier
        );
    }
}
