<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value;

class ExportRequest
{
    /** @var string */
    public $customerId;

    /** @var string */
    public $licenseKey;

    /** @var string */
    public $path;

    /** @var int */
    public $hidden;

    /** @var string */
    public $image;

    /** @var string */
    public $siteAccess;

    /** @var string */
    public $webHook;

    /** @var string */
    public $transaction;

    /** @var string */
    public $fields;

    /** @var int */
    public $pageSize;

    /** @var int */
    public $page;

    /** @var string */
    public $documentRoot;

    /** @var string */
    public $host;

    /** @var string */
    public $lang;

    /** @var int */
    public $mandatorId;

    /** @var array */
    public $contentTypeIdList;

    /**
     * @return array
     */
    public function getExportRequestParameters(): array
    {
        return get_object_vars($this);
    }
}
