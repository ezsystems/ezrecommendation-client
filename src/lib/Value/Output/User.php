<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value\Output;

use Webmozart\Assert\Assert;

class User
{
    /** \EzSystems\EzRecommendationClient\Value\Output\Attribute[] */
    private $attributes = [];

    /** @var string */
    private $userId;

    /**
     * @param string $userId
     * @param \EzSystems\EzRecommendationClient\Value\Output\Attribute[] $attributes
     */
    public function __construct(string $userId, array $attributes = [])
    {
        Assert::allIsInstanceOf($attributes, Attribute::class);

        $this->userId = $userId;
        $this->attributes = $attributes;
    }

    /**
     * @return \EzSystems\EzRecommendationClient\Value\Output\Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }
}
