<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Personalization\Storage;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\QueryType\QueryType;
use EzSystems\EzRecommendationClient\Exception\ItemNotFoundException;
use EzSystems\EzRecommendationClient\Value\Storage\Item;
use EzSystems\EzRecommendationClient\Value\Storage\ItemList;
use EzSystems\EzRecommendationClient\Value\Storage\ItemType;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Ibexa\Contracts\Personalization\Storage\DataSourceInterface;
use Ibexa\Contracts\Personalization\Value\ItemInterface;
use Ibexa\Personalization\Content\DataResolverInterface;

final class ContentDataSource implements DataSourceInterface
{
    private SearchService $searchService;

    private ContentService $contentService;

    private QueryType $queryType;

    private DataResolverInterface $dataResolver;

    public function __construct(
        SearchService $searchService,
        ContentService $contentService,
        QueryType $queryType,
        DataResolverInterface $dataResolver
    ) {
        $this->searchService = $searchService;
        $this->contentService = $contentService;
        $this->queryType = $queryType;
        $this->dataResolver = $dataResolver;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function countItems(CriteriaInterface $criteria): int
    {
        $query = $this->queryType->getQuery(['criteria' => $criteria]);
        $query->limit = 0;
        $languageFilter = ['languages' => $criteria->getLanguages()];

        return $this->searchService->findContent($query, $languageFilter)->totalCount ?? 0;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function fetchItems(CriteriaInterface $criteria): iterable
    {
        $query = $this->queryType->getQuery(['criteria' => $criteria]);
        $query->performCount = false;
        $query->limit = $criteria->getLimit();
        $query->offset = $criteria->getOffset();
        $languageFilter = ['languages' => $criteria->getLanguages()];

        $items = [];

        foreach ($this->searchService->findContent($query, $languageFilter) as $hit) {
            $items[] = $this->createItem($hit->valueObject);
        }

        return new ItemList($items);
    }

    public function fetchItem(string $id, string $language): ItemInterface
    {
        try {
            return $this->createItem(
                $this->contentService->loadContent((int)$id, [$language])
            );
        } catch (UnauthorizedException | NotFoundException $exception) {
            throw new ItemNotFoundException($id, $language, 0, $exception);
        }
    }

    private function createItem(Content $content): ItemInterface
    {
        return new Item(
            (string)$content->id,
            ItemType::fromContentType($content->getContentType()),
            $content->contentInfo->getMainLanguage()->languageCode,
            $this->dataResolver->resolve($content)
        );
    }
}
