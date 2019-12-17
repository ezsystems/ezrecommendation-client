<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Value\Parameters;
use Psr\Log\LoggerInterface;

final class ContentHelper
{
    /** @var \eZ\Publish\Api\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \EzSystems\EzRecommendationClient\Helper\ContentTypeHelper */
    private $contentTypeHelper;

    /** @var \EzSystems\EzRecommendationClient\Helper\SiteAccessHelper */
    private $siteAccessHelper;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(
        ContentServiceInterface $contentService,
        LocationServiceInterface $locationService,
        SearchServiceInterface $searchService,
        ConfigResolverInterface $configResolver,
        ContentTypeHelper $contentTypeHelper,
        SiteAccessHelper $siteAccessHelper,
        LoggerInterface $logger
    ) {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->searchService = $searchService;
        $this->configResolver = $configResolver;
        $this->contentTypeHelper = $contentTypeHelper;
        $this->siteAccessHelper = $siteAccessHelper;
        $this->logger = $logger;
    }

    /**
     * Gets languageCodes based on $content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param int|null $versionNo
     *
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getLanguageCodes(ContentInfo $contentInfo, ?int $versionNo = null): array
    {
        $version = $this->contentService->loadVersionInfo($contentInfo, $versionNo);

        return $version->languageCodes;
    }

    /**
     * Generates the REST URI of content $contentId.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string|null $lang
     *
     * @return string
     */
    public function getContentUri(ContentInfo $contentInfo, ?string $lang = null): string
    {
        return sprintf(
            '%s/api/ezp/v2/ez_recommendation/v1/content/%s%s',
            $this->configResolver->getParameter('host_uri', Parameters::NAMESPACE),
            $contentInfo->id,
            isset($lang) ? '?lang=' . $lang : ''
        );
    }

    /**
     * @param int $contentId
     * @param array|null $languages
     * @param int|null $versionNo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content|null
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getContent(int $contentId, ?array $languages = null, ?int $versionNo = null): ?Content
    {
        try {
            return $this->contentService->loadContent($contentId, $languages, $versionNo);
        } catch (NotFoundException $exception) {
            $this->logger->error(sprintf('Error while loading Content: %d, message: %s', $contentId, $exception->getMessage()));
            // this is most likely a internal draft, or otherwise invalid, ignoring
            return null;
        }
    }

    /**
     * @param int $contentId
     * @param array|null $languages
     * @param int|null $versionNo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content|null
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getIncludedContent(int $contentId, ?array $languages = null, ?int $versionNo = null): ?Content
    {
        $content = $this->getContent($contentId, $languages, $versionNo);

        return !$this->contentTypeHelper->isContentTypeExcluded($content->contentInfo) ? $content : null;
    }

    /**
     * Returns total amount of content based on ContentType ids.
     *
     * @param int $contentTypeId
     * @param array $options
     *
     * @return int|null
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function countContentItemsByContentTypeId(int $contentTypeId, array $options): ?int
    {
        $query = $this->getQuery($contentTypeId, $options);
        $query->limit = 0;

        return $this->searchService->findContent(
            $query,
            (!empty($options['lang']) ? array('languages' => array($options['lang'])) : array())
        )->totalCount;
    }

    /**
     * @param int $contentTypeId
     * @param array $options
     *
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getContentItems(int $contentTypeId, array $options): array
    {
        $query = $this->getQuery($contentTypeId, $options);
        $query->limit = (int) $options['pageSize'];
        $query->offset = $options['page'] * $options['pageSize'] - $options['pageSize'];

        return $this->searchService->findContent(
            $query,
            (!empty($options['lang']) ? array('languages' => array($options['lang'])) : array())
        )->searchHits;
    }

    /**
     * @param int $contentTypeId
     * @param array $options
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getQuery(int $contentTypeId, array $options): Query
    {
        $criteria = [
            new Criterion\ContentTypeId($contentTypeId),
        ];

        if ($options['path']) {
            $criteria[] = new Criterion\Subtree($options['path']);
        }

        if (!$options['hidden']) {
            $criteria[] = new Criterion\Visibility(Criterion\Visibility::VISIBLE);
        }

        $criteria[] = $this->generateSubtreeCriteria((int)$options['customerId'], $options['siteaccess']);

        $query = new Query();
        $query->query = new Criterion\LogicalAnd($criteria);

        return $query;
    }

    /**
     * Generates Criterions based on mandatoryId or requested siteAccess.
     *
     * @param int|null $customerId
     * @param string|null $siteAccess
     *
     * @return Criterion\LogicalOr
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function generateSubtreeCriteria(?int $customerId, ?string $siteAccess = null): Criterion\LogicalOr
    {
        $siteAccesses = $this->siteAccessHelper->getSiteAccesses($customerId, $siteAccess);

        $subtreeCriteria = [];
        $rootLocations = $this->siteAccessHelper->getRootLocationsBySiteAccesses($siteAccesses);
        foreach ($rootLocations as $rootLocationId) {
            $subtreeCriteria[] = new Criterion\Subtree($this->locationService->loadLocation($rootLocationId)->pathString);
        }

        return new Criterion\LogicalOr($subtreeCriteria);
    }
}
