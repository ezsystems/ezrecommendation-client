<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Content;

use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\Values\ContentType\ContentType as ApiContentType;
use eZ\Publish\API\Repository\Values\Content\Content as ApiContent;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Helper\TranslationHelper;
use EzSystems\EzRecommendationClient\Field\Value;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\RouterInterface;

class Content
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    protected $contentService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    protected $contentTypeService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    protected $locationService;

    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    protected $generator;

    /** @var \EzSystems\EzRecommendationClient\Field\Value */
    protected $value;

    /** @var \eZ\Publish\Core\Helper\TranslationHelper */
    private $translationHelper;

    /** @var int $defaultAuthorId */
    private $defaultAuthorId;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \Symfony\Component\Routing\RouterInterface $routingGenerator
     * @param \EzSystems\EzRecommendationClient\Field\Value $value
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     * @param int $defaultAuthorId
     */
    public function __construct(
        ContentServiceInterface $contentService,
        ContentTypeServiceInterface $contentTypeService,
        LocationServiceInterface $locationService,
        RouterInterface $routingGenerator,
        Value $value,
        TranslationHelper $translationHelper,
        int $defaultAuthorId
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->generator = $routingGenerator;
        $this->value = $value;
        $this->translationHelper = $translationHelper;
        $this->defaultAuthorId = $defaultAuthorId;
    }

    /**
     * Prepare content array.
     *
     * @param array $data
     * @param \Symfony\Component\HttpFoundation\ParameterBag $options
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     *
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function prepareContent(array $data, ParameterBag $options, ?OutputInterface $output = null): array
    {
        if ($output === null) {
            $output = new NullOutput();
        }

        $content = [];

        foreach ($data as $contentTypeId => $items) {
            $progress = new ProgressBar($output, count($items));
            $progress->start();

            foreach ($items as $contentValue) {
                $contentValue = $contentValue->valueObject;
                $contentType = $this->contentTypeService->loadContentType($contentValue->contentInfo->contentTypeId);
                $location = $this->locationService->loadLocation($contentValue->contentInfo->mainLocationId);
                $language = $options->get('lang', $location->contentInfo->mainLanguageCode);
                $this->value->setFieldDefinitionsList($contentType);
                $uriParams = ['siteaccess' => $this->translationHelper->getTranslationSiteAccess($language)];

                $content[$contentTypeId][$contentValue->id] = [
                    'contentId' => $contentValue->id,
                    'contentTypeId' => $contentType->id,
                    'identifier' => $contentType->identifier,
                    'language' => $language,
                    'publishedDate' => $contentValue->contentInfo->publishedDate->format('c'),
                    'author' => $this->getAuthor($contentValue, $contentType),
                    'uri' => $this->generator->generate($location, $uriParams, false),
                    'mainLocation' => [
                        'href' => '/api/ezp/v2/content/locations' . $location->pathString,
                    ],
                    'locations' => [
                        'href' => '/api/ezp/v2/content/objects/' . $contentValue->id . '/locations',
                    ],
                    'categoryPath' => $location->pathString,
                    'fields' => [],
                ];

                $fields = $this->prepareFields($contentType, $options->get('fields'));
                if (!empty($fields)) {
                    foreach ($fields as $field) {
                        $field = $this->value->getConfiguredFieldIdentifier($field, $contentType);
                        $content[$contentTypeId][$contentValue->id]['fields'][$field] =
                            $this->value->getFieldValue($contentValue, $field, $language, $options->all());
                    }
                }

                $progress->advance();
            }

            $progress->finish();
            $output->writeln('');
        }

        return $content;
    }

    /**
     * Returns author of the content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $contentValue
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return string
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getAuthor(ApiContent $contentValue, ApiContentType $contentType): string
    {
        $author = $contentValue->getFieldValue(
            $this->value->getConfiguredFieldIdentifier('author', $contentType)
        );

        if (null === $author) {
            try {
                $ownerId = empty($contentValue->contentInfo->ownerId) ? $this->defaultAuthorId : $contentValue->contentInfo->ownerId;
                $userContentInfo = $this->contentService->loadContentInfo($ownerId);
                $author = $userContentInfo->name;
            } catch (UnauthorizedException $e) {
                $author = '';
            }
        }

        return (string) $author;
    }

    /**
     * Checks if fields are given, if not - returns all of them.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param string $fields
     *
     * @return array
     */
    private function prepareFields(ApiContentType $contentType, ?string $fields): array
    {
        if ($fields !== null) {
            if (strpos($fields, ',') !== false) {
                return explode(',', $fields);
            }

            return [$fields];
        }

        $fields = [];
        $contentFields = $contentType->getFieldDefinitions();

        foreach ($contentFields as $field) {
            $fields[] = $field->identifier;
        }

        return $fields;
    }
}
