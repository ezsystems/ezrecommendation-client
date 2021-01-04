<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Request;

use EzSystems\EzRecommendationClient\SPI\Request;

class ExportNotifierRequest extends Request
{
    const ACTION_KEY = 'action';
    const FORMAT_KEY = 'format';
    const CONTENT_TYPE_ID_KEY = 'contentTypeId';
    const CONTENT_TYPE_NAME_KEY = 'contentTypeName';
    const LANG_KEY = 'lang';
    const URI_KEY = 'uri';
    const CREDENTIALS_KEY = 'credentials';

    /** @var string */
    public $action;

    /** @var string */
    public $format;

    /** @var int */
    public $contentTypeId;

    /** @var string */
    public $contentTypeName;

    /** @var string|null */
    public $lang;

    /** @var string */
    public $uri;

    /** @var array */
    public $credentials;

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
    public function getRequestAttributes(): array
    {
        return [
            'action' => $this->action,
            'format' => $this->format,
            'contentTypeId' => $this->contentTypeId,
            'contentTypeName' => $this->contentTypeName,
            'lang' => $this->lang,
            'uri' => $this->uri,
            'credentials' => $this->credentials,
        ];
    }
}
