<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Request;

use EzSystems\EzRecommendationClient\SPI\Request;

class EventNotifierRequest extends Request
{
    const ACTION_KEY = 'action';
    const FORMAT_KEY = 'format';
    const URI_KEY = 'uri';
    const ITEM_ID_KEY = 'itemId';
    const CONTENT_TYPE_ID_KEY = 'contentTypeId';
    const LANG_KEY = 'lang';
    const CREDENTIALS_KEY = 'credentials';

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

    /** @var array */
    public $credentials;

    public function __construct(array $parameters)
    {
        parent::__construct($this, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestAttributes(): array
    {
        return [
            'action' => $this->action,
            'format' => $this->format,
            'uri' => $this->uri,
            'itemId' => $this->itemId,
            'contentTypeId' => $this->contentTypeId,
            'lang' => $this->lang,
            'credentials' => $this->credentials,
        ];
    }
}
