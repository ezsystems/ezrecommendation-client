<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value\Storage;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Personalization\Value\ItemTypeInterface;

final class ItemType implements ItemTypeInterface
{
    private string $identifier;

    private string $name;

    public function __construct(string $identifier, string $name)
    {
        $this->identifier = $identifier;
        $this->name = $name;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function fromContentType(ContentType $contentType): self
    {
        return new self(
            $contentType->identifier,
            $contentType->getName() ?? $contentType->identifier
        );
    }
}
