<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\SPI;

use eZ\Publish\API\Repository\Values\ValueObject;
use Symfony\Component\Validator\Constraints as Assert;

abstract class Content extends ValueObject
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank(allowNull = true)
     */
    public $lang;

    /**
     * @var string[]
     *
     * @Assert\NotBlank(allowNull = true)
     * @Assert\All({
     *      @Assert\NotBlank
     * })
     */
    public $fields;

    public function getProperties($dynamicProperties = []): array
    {
        return get_object_vars($this);
    }
}
