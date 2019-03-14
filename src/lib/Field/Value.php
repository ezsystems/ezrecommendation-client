<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Field;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use EzSystems\EzRecommendationClient\Exception\InvalidRelationException;
use EzSystems\EzRecommendationClient\Mapper\RelationMapper;
use Psr\Log\LoggerInterface;
use Exception;

class Value
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \EzSystems\EzRecommendationClient\Field\TypeValue */
    private $typeValue;

    /** @var array */
    private $parameters;

    /** @var \EzSystems\EzRecommendationClient\Mapper\RelationMapper */
    private $relationMapper;

    /** @var array */
    private $fieldDefIdentifiers;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \EzSystems\EzRecommendationClient\Field\TypeValue $typeValue
     * @param \EzSystems\EzRecommendationClient\Mapper\RelationMapper $relationMapper
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $parameters
     */
    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        TypeValue $typeValue,
        RelationMapper $relationMapper,
        LoggerInterface $logger,
        array $parameters
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->typeValue = $typeValue;
        $this->relationMapper = $relationMapper;
        $this->logger = $logger;
        $this->parameters = $parameters;
    }

    /**
     * Returns parsed field value.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $field
     * @param string $language
     * @param array $options
     *
     * @return string
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getFieldValue(Content $content, string $field, string $language, array $options = []): string
    {
        $fieldObj = $content->getField($field, $language);

        if (!$fieldObj) {
            $fieldObj = $content->getField($field);
        }

        $contentType = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
        $imageFieldIdentifier = $this->getImageFieldIdentifier($content->id, $language);

        $relatedContentId = $this->getRelation($content, $fieldObj->fieldDefIdentifier, $language);
        $mapping = $this->relationMapper->getMapping($contentType->identifier, $field);

        try {
            if ($relatedContentId && $mapping) {
                $relatedContent = $this->contentService->loadContent($relatedContentId);

                if ($relatedContent && $relatedContent->versionInfo->contentInfo->published) {
                    $relatedContentType = $this->contentTypeService->loadContentType($relatedContent->contentInfo->contentTypeId);

                    if ($relatedContentType->identifier != $mapping['content']) {
                        throw new InvalidRelationException(
                            sprintf(
                                "Invalid relation: field '%s:%s' (object: %s, field: %s) has improper relation to object '%s' (object: %s) but '%s:%s' expected.",
                                $contentType->identifier,
                                $field,
                                $content->id,
                                $fieldObj->id,
                                $relatedContentType->identifier,
                                $relatedContentId,
                                $mapping['content'],
                                $mapping['field']
                            )
                        );
                    }
                    $relatedField = $content->getField($mapping['field'], $language);
                    $value = $relatedField ? $this->getParsedFieldValue($relatedField, $relatedContent, $language, $imageFieldIdentifier, $options) : '';
                } else {
                    $value = '';
                }
            } else {
                $value = $fieldObj ? $this->getParsedFieldValue($fieldObj, $content, $language, $imageFieldIdentifier, $options) : '';
            }
        } catch (InvalidRelationException $exception) {
            $this->logger->warning($exception->getMessage());

            $value = '';
        }

        return $value;
    }

    /**
     * Returns field name.
     *
     * To define another field name for specific value (e. g. author) add it to parameters.yml
     *
     * For example:
     *
     *     ez_recommendation.field_identifiers:
     *         author:
     *             blog_post: authors
     *         image:
     *             blog_post: thumbnail
     *
     * @param string $fieldName
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return string
     */
    public function getConfiguredFieldIdentifier(string $fieldName, ContentType $contentType): string
    {
        $contentTypeName = $contentType->identifier;

        if (isset($this->parameters['fieldIdentifiers'])) {
            $fieldIdentifiers = $this->parameters['fieldIdentifiers'];

            if (isset($fieldIdentifiers[$fieldName]) && !empty($fieldIdentifiers[$fieldName][$contentTypeName])) {
                return $fieldIdentifiers[$fieldName][$contentTypeName];
            }
        }

        return $fieldName;
    }

    /**
     * Prepares an array with field type identifiers.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     */
    public function setFieldDefinitionsList(ContentType $contentType): void
    {
        foreach ($contentType->fieldDefinitions as $fieldDef) {
            $this->fieldDefIdentifiers[$fieldDef->identifier] = $fieldDef->fieldTypeIdentifier;
        }
    }

    /**
     * Return identifier of a field of ezimage type.
     *
     * @param $contentId
     * @param string $language
     * @param bool $related
     *
     * @return string
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getImageFieldIdentifier($contentId, string $language, bool $related = false): string
    {
        $content = $this->contentService->loadContent($contentId);
        $contentType = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);

        $fieldDefinitions = $this->getFieldDefinitionList();
        $fieldNames = array_flip($fieldDefinitions);

        if (in_array('ezimage', $fieldDefinitions)) {
            return $fieldNames['ezimage'];
        } elseif (in_array('ezobjectrelation', $fieldDefinitions) && !$related) {
            $field = $content->getFieldValue($fieldNames['ezobjectrelation'], $language);

            if (!empty($field->destinationContentId)) {
                return $this->getImageFieldIdentifier($field->destinationContentId, $language, true);
            }
        } else {
            return $this->getConfiguredFieldIdentifier('image', $contentType);
        }
    }

    /**
     * Checks if content has image relation field, returns its ID if true.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $field
     * @param string $language
     *
     * @return int|null
     */
    private function getRelation(Content $content, string $field, string $language): ?int
    {
        $fieldDefinitions = $this->getFieldDefinitionList();
        $fieldNames = array_flip($fieldDefinitions);
        $isRelation = (in_array('ezobjectrelation', $fieldDefinitions) && $field == $fieldNames['ezobjectrelation']);

        if ($isRelation && $field == $fieldNames['ezobjectrelation']) {
            $fieldValue = $content->getFieldValue($fieldNames['ezobjectrelation'], $language);

            if (isset($fieldValue->destinationContentId)) {
                return $fieldValue->destinationContentId;
            }
        }

        return null;
    }

    /**
     * Returns field definitions.
     *
     * @return array
     */
    private function getFieldDefinitionList(): array
    {
        return $this->fieldDefIdentifiers;
    }

    /**
     * Returns parsed field value.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $language
     * @param string $imageFieldIdentifier
     * @param array $options
     *
     * @return string
     */
    private function getParsedFieldValue(Field $field, Content $content, string $language, string $imageFieldIdentifier, array $options = []): string
    {
        try {
            $this->logger->debug(sprintf(
                'Fetching field content for contentId %s, fieldId %s, fieldName %s',
                $content->id,
                $field->id,
                $field->fieldDefIdentifier
            ));

            $fieldType = $this->fieldDefIdentifiers[$field->fieldDefIdentifier];

            return $this->typeValue->$fieldType($field, $content, $language, $imageFieldIdentifier, $options);
        } catch (Exception $e) {
            $this->logger->warning(sprintf(
                'Unable to fetch field content for contentId %s, fieldId %s, fieldName %s (original exception: %s)',
                $content->id,
                $field->id,
                $field->fieldDefIdentifier,
                $e->getMessage()
            ));
        }
    }
}
