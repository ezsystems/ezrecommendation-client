<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Personalization\Value;

interface ItemInterface
{
    public function getId(): string;

    public function getType(): ItemTypeInterface;

    public function getLanguage(): string;

    /**
     * @return array<string, string|int|array>
     */
    public function getAttributes(): array;
}
