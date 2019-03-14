<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\MVC\Symfony\Routing\ChainRouter;
use EzSystems\EzRecommendationClient\Event\RecommendationResponseEvent;
use EzSystems\EzRecommendationClient\Value\RecommendationItem;
use EzSystems\EzRecommendationClient\Value\RecommendationMetadata;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

class RecommendationRandomContentEventSubscriber implements EventSubscriberInterface
{
    /** @var \ eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \ eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\Core\MVC\Symfony\Routing\ChainRouter */
    private $router;

    /** @var string[] */
    private $randomContentTypes;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $searchService
     * @param \eZ\Publish\API\Repository\SearchService $contentService
     * @param eZ\Publish\Core\MVC\Symfony\Routing\ChainRouter $chainRouter
     * @param array
     */
    public function __construct(
        SearchServiceInterface $searchService,
        ContentServiceInterface $contentService,
        ChainRouter $chainRouter,
        array $randomContentTypes
    ) {
        $this->searchService = $searchService;
        $this->contentService = $contentService;
        $this->router = $chainRouter;
        $this->randomContentTypes = $randomContentTypes;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RecommendationResponseEvent::NAME => ['onRecommendationResponse', -10],
        ];
    }

    /**
     * @param \EzSystems\EzRecommendationClient\Event\RecommendationResponseEvent $event
     */
    public function onRecommendationResponse(RecommendationResponseEvent $event): void
    {
        if (!$event->getRecommendationItems()) {
            $params = $event->getParameterBag();

            $randomContent = $this->getRandomContent(
                $this->getQuery($this->randomContentTypes),
                (int) $params->get(RecommendationMetadata::LIMIT)
            );

            $event->setRecommendationItems($this->getRandomRecommendationItems($randomContent));
        }
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param int $limit
     *
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getRandomContent(LocationQuery $query, int $limit): array
    {
        $results = $this->searchService->findLocations($query);

        shuffle($results->searchHits);

        $items = [];
        foreach ($results->searchHits as $item) {
            $items[] = $this->contentService->loadContentByContentInfo(
                $item->valueObject->contentInfo
            );

            if (count($items) === $limit) {
                break;
            }
        }

        return $items;
    }

    /**
     * @param array $randomContent
     *
     * @return RecommendationItem[]
     */
    private function getRandomRecommendationItems(array $randomContent): array
    {
        $randomRecommendationItems = [];
        $recommendationItemPrototype = new RecommendationItem();

        foreach ($randomContent as $content) {
            /** @var \DOMDocument $intro */
            $intro = $content->getFieldValue('intro')->xml;

            $recommendationItem = clone $recommendationItemPrototype;
            $recommendationItem->itemId = $content->id;
            $recommendationItem->title = $content->contentInfo->name;
            $recommendationItem->uri = $this->router->generate('ez_urlalias', ['contentId' => $content->id]);
            $recommendationItem->image = $content->getFieldValue('image')->uri ?? '';
            $recommendationItem->intro = $intro->textContent ?? '';

            $randomRecommendationItems[] = $recommendationItem;
        }

        return $randomRecommendationItems;
    }

    /**
     * Returns LocationQuery object based on given arguments.
     *
     * @param array $selectedContentTypes
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationQuery
     */
    private function getQuery(array $selectedContentTypes): LocationQuery
    {
        $query = new LocationQuery();

        $query->query = new Criterion\LogicalAnd([
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\ContentTypeIdentifier($selectedContentTypes),
        ]);

        return $query;
    }
}
