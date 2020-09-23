<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Field;

use eZ\Publish\Core\FieldType\RichText\Converter as RichTextConverterInterface;
use eZ\Publish\Core\MVC\Exception\SourceImageNotFoundException;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\XmlText\Converter\Html5 as XmlHtml5;
use EzSystems\EzRecommendationClient\Helper\ImageHelper;
use LogicException;

class TypeValue
{
    /** @var \EzSystems\EzRecommendationClient\Helper\ImageHelper */
    private $imageHelper;

    /** @var \eZ\Publish\Core\FieldType\RichText\Converter */
    private $richHtml5Converter;

    /** @var \eZ\Publish\Core\FieldType\XmlText\Converter\Html5 */
    private $xmlHtml5Converter;

    /**
     * @param \EzSystems\EzRecommendationClient\Helper\ImageHelper $imageHelper
     * @param \eZ\Publish\Core\FieldType\RichText\Converter $richHtml5Converter
     * @param \eZ\Publish\Core\FieldType\XmlText\Converter\Html5 $xmlHtml5Converter
     */
    public function __construct(
        ImageHelper $imageHelper,
        RichTextConverterInterface $richHtml5Converter,
        ?XmlHtml5 $xmlHtml5Converter
    ) {
        $this->imageHelper = $imageHelper;
        $this->richHtml5Converter = $richHtml5Converter;
        $this->xmlHtml5Converter = $xmlHtml5Converter;
    }

    /**
     * Default field value parsing.
     *
     * @param string $fieldName
     * @param mixed $args
     *
     * @return string
     */
    public function __call($fieldName, $args): string
    {
        $field = array_shift($args);

        return (string) $field->value;
    }

    /**
     * Method for parsing ezxmltext field.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     *
     * @return string
     */
    public function ezxmltext(Field $field): string
    {
        try {
            $xml = $this->xmlHtml5Converter->convert($field->value->xml);
        } catch (LogicException $e) {
            $xml = $field->value->xml->saveHTML();
        }

        return '<![CDATA[' . $xml . ']]>';
    }

    /**
     * Method for parsing ezrichtext field.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     *
     * @return string
     */
    public function ezrichtext(Field $field): string
    {
        return '<![CDATA[' . $this->richHtml5Converter->convert($field->value->xml)->saveHTML() . ']]>';
    }

    /**
     * Method for parsing ezimage field.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $language
     * @param string $imageFieldIdentifier
     * @param array $options
     *
     * @return string
     */
    public function ezimage(Field $field, Content $content, string $language, string $imageFieldIdentifier, array $options = []): string
    {
        if (!isset($field->value->id)) {
            return '';
        }

        try {
            return $this->imageHelper->getImageUrl($field, $content, $options);
        } catch (SourceImageNotFoundException $exception) {
            return '';
        }
    }

    /**
     * Method for parsing ezimageasset field.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $language
     * @param string $imageFieldIdentifier
     * @param array $options
     *
     * @return string
     */
    public function ezimageasset(Field $field, Content $content, string $language, string $imageFieldIdentifier, array $options = []): string
    {
        if (!isset($field->value->destinationContentId)) {
            return '';
        }

        return $this->imageHelper->getImageUrl($field, $content, $options) ?? '';
    }

    /**
     * Method for parsing ezobjectrelation field.
     * For now related fields refer to images.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $language
     * @param string $imageFieldIdentifier
     * @param array $options
     *
     * @return string
     */
    public function ezobjectrelation(Field $field, Content $content, string $language, string $imageFieldIdentifier, array $options = []): string
    {
        $fields = $content->getFieldsByLanguage($language);
        foreach ($fields as $type => $field) {
            if ($type == $imageFieldIdentifier) {
                return $this->ezimage($field, $content, $language, $imageFieldIdentifier, $options);
            }
        }

        return '';
    }
}
