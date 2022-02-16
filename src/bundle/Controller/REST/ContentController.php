<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Controller\REST;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformRest\Server\Controller as RestController;
use EzSystems\EzPlatformRest\Server\Exceptions\AuthenticationFailedException;
use EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface;
use EzSystems\EzRecommendationClient\Service\ContentServiceInterface;
use EzSystems\EzRecommendationClient\Value\Content;
use EzSystems\EzRecommendationClient\Value\ContentData;
use Ibexa\Personalization\QueryType\ContentQueryType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ContentController extends RestController
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface */
    private $authenticator;

    /** @var \Ibexa\Personalization\QueryType\ContentQueryType */
    private $contentQueryType;

    /** @var \EzSystems\EzRecommendationClient\Service\ContentServiceInterface */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    public function __construct(
        AuthenticatorInterface $authenticator,
        ContentQueryType $contentQueryType,
        ContentServiceInterface $contentService,
        Repository $repository,
        SearchService $searchService
    ) {
        $this->authenticator = $authenticator;
        $this->contentQueryType = $contentQueryType;
        $this->contentService = $contentService;
        $this->repository = $repository;
        $this->searchService = $searchService;
    }

    /**
     * @throws \Exception
     */
    public function getContentByIdAction(int $contentId, Request $request): ContentData
    {
        if (!$this->authenticator->authenticate()) {
            throw new AuthenticationFailedException('Access denied: wrong credentials', Response::HTTP_UNAUTHORIZED);
        }

        $requestQuery = $request->query;
        $contentItems = $this->getContentItems(
            $this->contentQueryType->getQuery(
                [
                    'contentId' => $contentId,
                    'language' => $requestQuery->get('lang'),
                ]
            )
        );

        return $this->getContentData($contentItems);
    }

    /**
     * @throws \Exception
     */
    public function getContentByRemoteIdAction(string $remoteId, Request $request): ContentData
    {
        if (!$this->authenticator->authenticate()) {
            throw new AuthenticationFailedException('Access denied: wrong credentials', Response::HTTP_UNAUTHORIZED);
        }

        $requestQuery = $request->query;
        $contentItems = $this->getContentItems(
            $this->contentQueryType->getQuery(
                [
                    'contentId' => $remoteId,
                    'language' => $requestQuery->get('lang'),
                ]
            )
        );

        return $this->getContentData($contentItems);
    }

    /**
     * @return array<\eZ\Publish\API\Repository\Values\Content\Search\SearchHit>
     *
     * @throws \Exception
     */
    private function getContentItems(Query $query): array
    {
        return $this->repository->sudo(function () use ($query) {
            return $this->searchService->findContent($query)->searchHits;
        });
    }

    /**
     * @param array<\eZ\Publish\API\Repository\Values\Content\Search\SearchHit> $contentItems
     */
    private function getContentData(array $contentItems): ContentData
    {
        $contentData = $this->contentService->prepareContent(
            [$contentItems],
            new Content()
        );

        return new ContentData($contentData);
    }
}
