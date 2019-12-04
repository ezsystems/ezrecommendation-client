<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value;

use eZ\Publish\API\Repository\Values\ValueObject;
use Symfony\Component\Validator\Constraints as Assert;

class ExportParameters extends ValueObject
{
    /**
     * @var int
     *
     * @Assert\NotBlank
     * @Assert\Positive
     */
    public $customerId;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    public $licenseKey;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Url
     */
    public $webHook;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Url
     */
    public $host;

    /**
     * @var int[]
     *
     * @Assert\NotBlank
     * @Assert\All({
     *      @Assert\NotBlank,
     *      @Assert\Positive
     * })
     */
    public $contentTypeIdList;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(allowNull = true)
     */
    public $path;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(allowNull = true)
     */
    public $hidden;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(allowNull = true)
     */
    public $siteaccess;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(allowNull = true)
     */
    public $image;

    /**
     * @var string[]
     *
     * @Assert\NotBlank(allowNull = true)
     * @Assert\All({
     *      @Assert\NotBlank
     * })
     */
    public $fields;

    /**
     * @var int
     *
     * @Assert\Positive
     */
    public $pageSize;

    /**
     * @var int
     *
     * @Assert\Positive
     */
    public $page;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(allowNull = true)
     */
    public $lang;

    /**
     * @var string[]
     *
     * @Assert\NotBlank
     * @Assert\All({
     *      @Assert\NotBlank
     * })
     */
    public $languages;

    public function getProperties($dynamicProperties = []): array
    {
        return get_object_vars($this);
    }
}
