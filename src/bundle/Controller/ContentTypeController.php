<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Controller;

use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use EzSystems\EzPlatformRest\Server\Controller as RestController;
use EzSystems\EzPlatformRest\Server\Exceptions\AuthenticationFailedException;
use EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface;
use EzSystems\EzRecommendationClient\Helper\SiteAccessHelper;
use EzSystems\EzRecommendationClient\Service\ContentServiceInterface;
use EzSystems\EzRecommendationClient\Value\Content;
use EzSystems\EzRecommendationClient\Value\ContentData;
use EzSystems\EzRecommendationClient\Value\IdList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ContentTypeController extends RestController
{
    private const PAGE_SIZE = 10;

    /** @var \eZ\Publish\Core\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\Core\Repository\SearchService */
    private $searchService;

    /** @var \EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface */
    private $authenticator;

    /** @var \EzSystems\EzRecommendationClient\Service\ContentServiceInterface */
    private $contentService;

    /** @var \EzSystems\EzRecommendationClient\Helper\SiteAccessHelper */
    private $siteAccessHelper;

    public function __construct(
        LocationServiceInterface $locationService,
        SearchServiceInterface $searchService,
        AuthenticatorInterface $authenticator,
        ContentServiceInterface $contentService,
        SiteAccessHelper $siteAccessHelper
    ) {
        $this->locationService = $locationService;
        $this->searchService = $searchService;
        $this->authenticator = $authenticator;
        $this->contentService = $contentService;
        $this->siteAccessHelper = $siteAccessHelper;
    }

    /**
     * Prepares content for ContentData class.
     *
     * @param \EzSystems\EzRecommendationClient\Value\IdList $idList
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @ParamConverter("list_converter")
     *
     * @return \EzSystems\EzRecommendationClient\Value\ContentData
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getContentTypeAction(IdList $idList, Request $request): ContentData
    {
        if (!$this->authenticator->authenticate()) {
            throw new AuthenticationFailedException('Access denied: wrong credentials', Response::HTTP_UNAUTHORIZED);
        }

        $content = $this->prepareContentByContentTypeIds($idList->list, $request);

        return new ContentData($content);
    }

    /**
     * Returns paged content based on ContentType ids.
     *
     * @param array $contentTypeIds
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function prepareContentByContentTypeIds(array $contentTypeIds, Request $request): array
    {
        $requestQuery = $request->query;

        $content = new Content();
        $content->lang = $requestQuery->get('lang');
        $content->fields = $requestQuery->get('fields');

        $contentItems = [];

        foreach ($contentTypeIds as $contentTypeId) {
            $contentItems[$contentTypeId] = $this->searchService->findContent(
                $this->getQuery((int) $contentTypeId, $requestQuery),
                (!empty($content->lang) ? ['languages' => [$content->lang]] : [])
            )->searchHits;
        }

        return $this->contentService->prepareContent($contentItems, $content);
    }

    /**
     * @param int $contentTypeId
     * @param \Symfony\Component\HttpFoundation\ParameterBag $parameterBag
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getQuery(int $contentTypeId, ParameterBag $parameterBag): Query
    {
        $criteria = [new Criterion\ContentTypeId($contentTypeId)];

        if ($parameterBag->has('path')) {
            $criteria[] = new Criterion\Subtree($parameterBag->get('path'));
        }

        if (!$parameterBag->get('hidden')) {
            $criteria[] = new Criterion\Visibility(Criterion\Visibility::VISIBLE);
        }

        if ($parameterBag->has('sa')) {
            $rootLocationPathString = $this->locationService->loadLocation(
                $this->siteAccessHelper->getRootLocationBySiteAccessName($parameterBag->get('sa'))
            )->pathString;

            $criteria[] = new Criterion\Subtree($rootLocationPathString);
        }

        $query = new Query();

        $pageSize = (int) $parameterBag->get('page_size', self::PAGE_SIZE);
        $page = (int) $parameterBag->get('page', 1);
        $offset = $page * $pageSize - $pageSize;

        $query->query = new Criterion\LogicalAnd($criteria);
        $query->limit = $pageSize;
        $query->offset = $offset;

        return $query;
    }
}
