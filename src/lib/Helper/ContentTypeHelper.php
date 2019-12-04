<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Value\Parameters;

final class ContentTypeHelper
{
    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    public function __construct(
        ContentTypeServiceInterface $contentTypeService,
        ContentServiceInterface $contentService,
        RepositoryInterface $repository,
        ConfigResolverInterface $configResolver
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->contentService = $contentService;
        $this->configResolver = $configResolver;
        $this->repository = $repository;
    }

    /**
     * Returns ContentType ID based on $contentType name.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function getContentTypeId(string $contentType): int
    {
        return $this->contentTypeService->loadContentTypeByIdentifier($contentType)->id;
    }

    /**
     * Returns ContentType identifier based on $contentId.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getContentTypeIdentifier(int $contentId): string
    {
        return $this->contentTypeService->loadContentType(
            $this->contentService
                ->loadContent($contentId)
                ->contentInfo
                ->contentTypeId
        )->identifier;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * 
     * @return bool
     *
     * @throws \Exception
     */
    public function isContentTypeExcluded(Content $content): bool
    {
        $contentType = $this->repository->sudo(function () use ($content) {
            return $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
        });

        return !in_array(
            $contentType->identifier,
            $this->configResolver->getParameter('included_content_types', Parameters::NAMESPACE)
        );
    }
}
