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
use eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Server\Exceptions\AuthenticationFailedException;
use EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface;
use EzSystems\EzRecommendationClient\Content\Content;
use EzSystems\EzRecommendationClient\Helper\ParameterHelper;
use EzSystems\EzRecommendationClient\Helper\SiteAccessHelper;
use EzSystems\EzRecommendationClient\Value\ContentData;
use EzSystems\EzRecommendationClient\Value\IdList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentTypeController extends Controller
{
    private const PAGE_SIZE = 10;

    /** @var \eZ\Publish\Core\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\Core\Repository\SearchService */
    private $searchService;

    /** @var \EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface */
    private $authenticator;

    /** @var \EzSystems\EzRecommendationClient\Content\Content */
    private $content;

    /** @var \EzSystems\EzRecommendationClient\Helper\SiteAccessHelper */
    private $siteAccessHelper;

    /** @var \EzSystems\EzRecommendationClient\Helper\ParameterHelper */
    private $parameterHelper;

    /**
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param AuthenticatorInterface $authenticator
     * @param \EzSystems\EzRecommendationClient\Content\Content $content
     * @param \EzSystems\EzRecommendationClient\Helper\SiteAccessHelper $siteAccessHelper
     * @param \EzSystems\EzRecommendationClient\Helper\ParameterHelper $parameterHelper
     */
    public function __construct(
        LocationServiceInterface $locationService,
        SearchServiceInterface $searchService,
        AuthenticatorInterface $authenticator,
        Content $content,
        SiteAccessHelper $siteAccessHelper,
        ParameterHelper $parameterHelper
    ) {
        $this->locationService = $locationService;
        $this->searchService = $searchService;
        $this->authenticator = $authenticator;
        $this->content = $content;
        $this->siteAccessHelper = $siteAccessHelper;
        $this->parameterHelper = $parameterHelper;
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
        $options = $this->parameterHelper->parseParameters($request->query, ['page_size', 'page', 'path', 'hidden', 'lang', 'sa', 'image']);

        $lang = $options->get('lang');

        $contentItems = [];

        foreach ($contentTypeIds as $contentTypeId) {
            $contentItems[$contentTypeId] = $this->searchService->findContent(
                $this->getQuery((int) $contentTypeId, $options),
                (!empty($lang) ? ['languages' => [$lang]] : [])
            )->searchHits;
        }

        $contentOptions = $this->parameterHelper->parseParameters($request->query, ['lang', 'fields', 'image']);

        return $this->content->prepareContent($contentItems, $contentOptions);
    }

    /**
     * @param ParameterBag $parameterBag
     * @param int $contentTypeId
     *
     * @return Query
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
