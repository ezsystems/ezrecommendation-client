<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\SPI\FieldType\Value;
use EzSystems\EzPlatformRichText\eZ\FieldType\RichText\Value as RichTextValue;
use EzSystems\EzRecommendationClient\Event\RecommendationResponseEvent;
use EzSystems\EzRecommendationClient\Helper\ImageHelper;
use EzSystems\EzRecommendationClient\Request\BasicRecommendationRequest;
use EzSystems\EzRecommendationClient\Value\Parameters;
use EzSystems\EzRecommendationClient\Value\RecommendationItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

final class RecommendationRandomContentEventSubscriber implements EventSubscriberInterface
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    /** @var \EzSystems\EzRecommendationClient\Helper\ImageHelper */
    private $imageHelper;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $searchService
     * @param \eZ\Publish\API\Repository\SearchService $contentService
     */
    public function __construct(
        SearchServiceInterface $searchService,
        ContentServiceInterface $contentService,
        ConfigResolverInterface $configResolver,
        RouterInterface $router,
        ImageHelper $imageHelper
    ) {
        $this->searchService = $searchService;
        $this->contentService = $contentService;
        $this->configResolver = $configResolver;
        $this->router = $router;
        $this->imageHelper = $imageHelper;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RecommendationResponseEvent::class => ['onRecommendationResponse', -10],
        ];
    }

    /**
     * @throws NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onRecommendationResponse(RecommendationResponseEvent $event): void
    {
        if (!$event->getRecommendationItems()) {
            $params = $event->getParameterBag();

            $randomContentTypes = $this->configResolver->getParameter('random_content_types', Parameters::NAMESPACE);

            if (!$randomContentTypes) {
                return;
            }

            $randomContent = $this->getRandomContent(
                $this->getQuery($randomContentTypes),
                (int) $params->get(BasicRecommendationRequest::LIMIT_KEY)
            );

            $event->setRecommendationItems($this->getRandomRecommendationItems($randomContent));
        }
    }

    /**
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

            if (\count($items) === $limit) {
                break;
            }
        }

        return $items;
    }

    /**
     * @return RecommendationItem[]
     */
    private function getRandomRecommendationItems(array $randomContent): array
    {
        $randomRecommendationItems = [];
        $recommendationItemPrototype = new RecommendationItem();

        foreach ($randomContent as $content) {
            $recommendationItem = clone $recommendationItemPrototype;
            $recommendationItem->itemId = $content->id;
            $recommendationItem->title = $content->contentInfo->name;
            $recommendationItem->uri = $this->router->generate('ez_urlalias', ['contentId' => $content->id]);
            $recommendationItem->intro = $this->getIntro($content);
            $recommendationItem->image = $this->getImage($content);

            $randomRecommendationItems[] = $recommendationItem;
        }

        return $randomRecommendationItems;
    }

    /**
     * Returns LocationQuery object based on given arguments.
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

    private function getIntro(Content $content): string
    {
        $value = $this->getFieldValue($content, 'intro');

        if ($value instanceof RichTextValue) {
            return $value->xml->textContent;
        }

        if ($value instanceof TextLineValue) {
            return $value->text;
        }

        return (string) $value;
    }

    private function getImage(Content $content): ?string
    {
        return $this->imageHelper->getImageUrl($content->getField('image'), $content, []);
    }

    /**
     * @return
     */
    private function getFieldValue(Content $content, string $fieldName): Value
    {
        $fieldIdentifiers = $this->configResolver->getParameter('identifiers', Parameters::NAMESPACE, 'field');
        $contentTypeIdentifier = $content->getContentType()->identifier;

        return isset($fieldIdentifiers[$fieldName][$contentTypeIdentifier]) ?
            $content->getFieldValue($fieldIdentifiers[$fieldName][$contentTypeIdentifier]) :
            $content->getFieldValue($fieldName);
    }
}
