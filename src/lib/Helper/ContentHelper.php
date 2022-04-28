<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
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
use Ibexa\Personalization\Config\Repository\RepositoryConfigResolverInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ContentHelper implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const UPDATE_CONTENT_URL_SUFFIX = '%s/api/ezp/v2/ez_recommendation/v1/content/%s/%s%s';
    private const CONTENT_ID_URL_PREFIX = 'id';
    private const CONTENT_REMOTE_ID_URL_PREFIX = 'remote-id';

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \EzSystems\EzRecommendationClient\Helper\ContentTypeHelper */
    private $contentTypeHelper;

    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \Ibexa\Personalization\Config\Repository\RepositoryConfigResolverInterface */
    private $repositoryConfigResolver;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \EzSystems\EzRecommendationClient\Helper\SiteAccessHelper */
    private $siteAccessHelper;

    public function __construct(
        ConfigResolverInterface $configResolver,
        ContentServiceInterface $contentService,
        ContentTypeHelper $contentTypeHelper,
        LocationServiceInterface $locationService,
        RepositoryConfigResolverInterface $repositoryConfigResolver,
        SearchServiceInterface $searchService,
        SiteAccessHelper $siteAccessHelper,
        ?LoggerInterface $logger = null
    ) {
        $this->configResolver = $configResolver;
        $this->contentService = $contentService;
        $this->contentTypeHelper = $contentTypeHelper;
        $this->locationService = $locationService;
        $this->repositoryConfigResolver = $repositoryConfigResolver;
        $this->searchService = $searchService;
        $this->siteAccessHelper = $siteAccessHelper;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Gets languageCodes based on $content.
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
     */
    public function getContentUri(ContentInfo $contentInfo, ?string $lang = null): string
    {
        $useRemoteId = $this->repositoryConfigResolver->useRemoteId();
        $contentId = $useRemoteId ? $contentInfo->remoteId : $contentInfo->id;
        $prefix = $useRemoteId ? self::CONTENT_REMOTE_ID_URL_PREFIX : self::CONTENT_ID_URL_PREFIX;
        $language = isset($lang) ? '?lang=' . $lang : '';

        return sprintf(
            self::UPDATE_CONTENT_URL_SUFFIX,
            $this->configResolver->getParameter('host_uri', Parameters::NAMESPACE),
            $prefix,
            $contentId,
            $language
        );
    }

    /**
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getIncludedContent(int $contentId, ?array $languages = null, ?int $versionNo = null): ?Content
    {
        $content = $this->getContent($contentId, $languages, $versionNo);
        if (null === $content) {
            return null;
        }

        return !$this->contentTypeHelper->isContentTypeExcluded($content->getContentType()) ? $content : null;
    }

    /**
     * Returns total amount of content based on ContentType ids.
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
            (!empty($options['lang']) ? ['languages' => [$options['lang']]] : [])
        )->totalCount;
    }

    /**
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
            (!empty($options['lang']) ? ['languages' => [$options['lang']]] : [])
        )->searchHits;
    }

    /**
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
