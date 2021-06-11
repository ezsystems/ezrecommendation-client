<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Personalization\Criteria;

interface CriteriaInterface
{
    /**
     * @return array<string>
     */
    public function getItemTypeIdentifiers(): array;

    /**
     * @return array<string>
     */
    public function getLanguages(): array;

    public function getLimit(): int;

    public function getOffset(): int;
}
