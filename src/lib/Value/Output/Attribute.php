<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value\Output;

use Webmozart\Assert\Assert;

class Attribute
{
    const TYPE_NUMERIC = 'NUMERIC';
    const TYPE_NOMINAL = 'NOMINAL';
    const TYPE_TEXT = 'TEXT';
    const TYPE_DATE = 'DATE';
    const TYPE_DATETIME = 'DATETIME';

    const DEFAULT_TYPE = self::TYPE_NOMINAL;

    const TYPES = [
        self::TYPE_NUMERIC,
        self::TYPE_NOMINAL,
        self::TYPE_TEXT,
        self::TYPE_DATE,
        self::TYPE_DATETIME,
    ];

    /** @var string */
    private $name;

    /** @var string */
    private $value;

    /** @var string */
    private $type;

    /**
     * @param string $name
     * @param string $value
     * @param string $type
     */
    public function __construct(string $name, string $value, string $type = self::DEFAULT_TYPE)
    {
        Assert::notNull($name);
        Assert::keyExists(array_flip(self::TYPES), $type, 'Wrong Attribute type.');

        $this->name = $name;
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
