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
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use EzSystems\EzPlatformRest\Server\Controller as RestController;
use EzSystems\EzPlatformRest\Server\Exceptions\AuthenticationFailedException;
use EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface;
use EzSystems\EzRecommendationClient\Service\ContentServiceInterface;
use EzSystems\EzRecommendationClient\Value\Content;
use EzSystems\EzRecommendationClient\Value\ContentData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ContentController extends RestController
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface */
    private $authenticator;

    /** @var \EzSystems\EzRecommendationClient\Service\ContentServiceInterface */
    private $contentService;

    public function __construct(
        Repository $repository,
        SearchService $searchService,
        AuthenticatorInterface $authenticator,
        ContentServiceInterface $contentService
    ) {
        $this->repository = $repository;
        $this->searchService = $searchService;
        $this->authenticator = $authenticator;
        $this->contentService = $contentService;
    }

    /**
     * Prepares content for ContentData class.
     *
     * @throws \Exception
     */
    public function getContentByIdAction(int $contentId, Request $request): ContentData
    {
        if (!$this->authenticator->authenticate()) {
            throw new AuthenticationFailedException('Access denied: wrong credentials', Response::HTTP_UNAUTHORIZED);
        }

        $requestQuery = $request->query;
        $language = (string) $requestQuery->get('lang');
        $criteria = [new Criterion\ContentId($contentId)];
        $contentItems = $this->getContentItems(
            $this->getQuery($criteria, $language),
            $language
        );

        return $this->getContentData($contentItems, $language);
    }

    /**
     * Prepares content for ContentData class.
     *
     * @throws \Exception
     */
    public function getContentByRemoteIdAction(string $remoteId, Request $request): ContentData
    {
        if (!$this->authenticator->authenticate()) {
            throw new AuthenticationFailedException('Access denied: wrong credentials', Response::HTTP_UNAUTHORIZED);
        }

        $requestQuery = $request->query;
        $language = (string) $requestQuery->get('lang');
        $criteria = [new Criterion\RemoteId($remoteId)];
        $contentItems = $this->getContentItems(
            $this->getQuery($criteria, $language),
            $language
        );

        return $this->getContentData($contentItems, $language);
    }

    /**
     * @return array<\eZ\Publish\API\Repository\Values\Content\Search\SearchHit>
     *
     * @throws \Exception
     */
    private function getContentItems(Query $query, string $language): array
    {
        return $this->repository->sudo(function () use ($query, $language) {
            return $this->searchService->findContent(
                $query,
                !empty($language) ? ['languages' => [$language]] : []
            )->searchHits;
        });
    }

    /**
     * @param array<\eZ\Publish\API\Repository\Values\Content\Search\SearchHit> $contentItems
     */
    private function getContentData(array $contentItems, string $language): ContentData
    {
        $contentData = $this->contentService->prepareContent(
            [$contentItems],
            new Content(
                [
                    'lang' => $language,
                ]
            )
        );

        return new ContentData($contentData);
    }

    /**
     * @param array<\eZ\Publish\API\Repository\Values\Content\Query\Criterion> $criteria
     */
    private function getQuery(array $criteria, string $language): Query
    {
        //@TODO should be moved to separate class

        if (!empty($language)) {
            $criteria[] = new Criterion\LanguageCode($language);
        }

        $query = new Query();
        $query->query = new Criterion\LogicalAnd($criteria);

        return $query;
    }
}
