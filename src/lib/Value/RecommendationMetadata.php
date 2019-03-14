<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value;

use EzSystems\EzRecommendationClient\Api\ApiMetadata;

final class RecommendationMetadata extends ApiMetadata
{
    const SCENARIO = 'scenario';
    const LIMIT = 'limit';
    const CONTEXT_ITEMS = 'contextItems';
    const CONTENT_TYPE = 'contentType';
    const OUTPUT_TYPE_ID = 'outputTypeId';
    const CATEGORY_PATH = 'categoryPath';
    const LANGUAGE = 'language';
    const ATTRIBUTES = 'attributes';
    const FILTERS = 'filters';

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        parent::__construct($this, $parameters);
    }

    /** @var string */
    public $scenario;

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

    /**
     * {@inheritdoc}
     */
    public function getMetadataAttributes(): array
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
        ];
    }

    /**
     * @return array
     */
    private function extractFilters(): array
    {
        $extractedFilters = [];

        foreach ($this->filters as $filterKey => $filterValue) {
            $filter = is_array($filterValue) ? implode(',', $filterValue) : $filterValue;
            $extractedFilters[] = [$filterKey => $filter];
        }

        return $extractedFilters;
    }
}
