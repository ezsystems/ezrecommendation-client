<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value;

use EzSystems\EzRecommendationClient\Api\ApiMetadata;

class EventNotifierMetadata extends ApiMetadata
{
    const ACTION = 'action';
    const FORMAT = 'format';
    const URI = 'uri';
    const ITEM_ID = 'itemId';
    const CONTENT_TYPE_ID = 'contentTypeId';
    const LANG = 'lang';

    /** @var string */
    public $action;

    /** @var string */
    public $format;

    /** @var string */
    public $uri;

    /** @var int */
    public $itemId;

    /** @var int */
    public $contentTypeId;

    /** @var string|null */
    public $lang;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        parent::__construct($this, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataAttributes(): array
    {
        return [
            'action' => $this->action,
            'format' => $this->format,
            'uri' => $this->uri,
            'itemId' => $this->itemId,
            'contentTypeId' => $this->contentTypeId,
            'lang' => $this->lang,
        ];
    }
}
