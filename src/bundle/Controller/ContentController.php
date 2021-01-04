<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Controller;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use EzSystems\EzPlatformRest\Server\Controller as RestController;
use EzSystems\EzPlatformRest\Server\Exceptions\AuthenticationFailedException;
use EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface;
use EzSystems\EzRecommendationClient\Helper\ParamsConverterHelper;
use EzSystems\EzRecommendationClient\Service\ContentServiceInterface;
use EzSystems\EzRecommendationClient\Value\Content;
use EzSystems\EzRecommendationClient\Value\ContentData;
use EzSystems\EzRecommendationClient\Value\IdList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ContentController extends RestController
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\Core\Repository\SearchService */
    private $searchService;

    /** @var \EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface */
    private $authenticator;

    /** @var \EzSystems\EzRecommendationClient\Service\ContentServiceInterface */
    private $contentService;

    public function __construct(
        Repository $repository,
        SearchServiceInterface $searchService,
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
     * @ParamConverter("list_converter")
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function getContentAction(IdList $idList, Request $request): ContentData
    {
        if (!$this->authenticator->authenticate()) {
            throw new AuthenticationFailedException('Access denied: wrong credentials', Response::HTTP_UNAUTHORIZED);
        }

        $requestQuery = $request->query;

        $content = new Content();
        $content->lang = $requestQuery->get('lang');
        $content->fields = $requestQuery->get('fields')
            ? ParamsConverterHelper::getArrayFromString($requestQuery->get('fields'))
            : null;

        $contentItems = $this->repository->sudo(function () use ($requestQuery, $idList, $content) {
            return $this->searchService->findContent(
                $this->getQuery($requestQuery, $idList),
                (!empty($content->lang) ? ['languages' => [$content->lang]] : [])
            )->searchHits;
        });
        $contentData = $this->contentService->prepareContent([$contentItems], $content);

        return new ContentData($contentData);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag ParameterBag $parameterBag
     */
    private function getQuery(ParameterBag $parameterBag, IdList $idList): Query
    {
        $criteria = [new Criterion\ContentId($idList->list)];

        if (!$parameterBag->get('hidden')) {
            $criteria[] = new Criterion\Visibility(Criterion\Visibility::VISIBLE);
        }

        if ($parameterBag->has('lang')) {
            $criteria[] = new Criterion\LanguageCode($parameterBag->get('lang'));
        }

        $query = new Query();
        $query->query = new Criterion\LogicalAnd($criteria);

        return $query;
    }
}
