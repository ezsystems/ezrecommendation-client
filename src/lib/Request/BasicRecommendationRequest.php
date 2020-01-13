<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Request;

use EzSystems\EzRecommendationClient\SPI\RecommendationRequest;

final class BasicRecommendationRequest extends RecommendationRequest
{
    const LIMIT_KEY = 'limit';
    const CONTEXT_ITEMS_KEY = 'contextItems';
    const CONTENT_TYPE_KEY = 'contentType';
    const OUTPUT_TYPE_ID_KEY = 'outputTypeId';
    const CATEGORY_PATH_KEY = 'categoryPath';
    const LANGUAGE_KEY = 'language';
    const ATTRIBUTES_KEY = 'attributes';
    const FILTERS_KEY = 'filters';
    const USE_CONTEXT_CATEGORY_PATH_KEY = 'usecontextcategorypath';
    const RECOMMEND_CATEGORY_KEY = 'recommendCategory';

    /** @var int */
    public $limit;

    /** @var int */
    public $contextItems;

    /** @var string */
    public $contentType;

    /** @var int */
    public $outputTypeId;

    /** @var string */
    public $categoryPath;

    /** @var string */
    public $language;

    /** @var array */
    public $attributes;

    /** @var array */
    public $filters;

    /** @var bool */
    public $usecontextcategorypath = false;

    /** @var bool */
    public $recommendCategory = false;

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
            'numrecs' => $this->limit,
            'contextitems' => $this->contextItems,
            'contenttype' => $this->contentType,
            'outputtypeid' => $this->outputTypeId,
            'categorypath' => $this->categoryPath,
            'lang' => $this->language,
            'attributes' => $this->getAdditionalAttributesToQueryString($this->attributes, 'attribute'),
            'filters' => $this->extractFilters(),
            'usecontextcategorypath' => $this->usecontextcategorypath,
            'recommendCategory' => $this->recommendCategory,
        ];
    }

    private function extractFilters(): array
    {
        $extractedFilters = [];

        foreach ($this->filters as $filterKey => $filterValue) {
            $filter = \is_array($filterValue) ? implode(',', $filterValue) : $filterValue;
            $extractedFilters[] = [$filterKey => $filter];
        }

        return $extractedFilters;
    }
}
