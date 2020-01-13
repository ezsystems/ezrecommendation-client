<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Mapper;

final class RelationMapper
{
    /** @var array $fieldMapping */
    private $fieldMappings;

    public function __construct(array $fieldMappings)
    {
        $this->fieldMappings = $fieldMappings;
    }

    /**
     * Get related mapping for specified content and field.
     *
     * @return array|null mixed Returns mathing mapping array or null if no matching mapping found
     */
    public function getMapping(string $contentTypeIdentifier, string $fieldIdentifier): ?array
    {
        $key = $contentTypeIdentifier . '.' . $fieldIdentifier;

        if (!isset($this->fieldMappings[$key])) {
            return null;
        }

        $identifier = explode('.', $this->fieldMappings[$key]);

        return [
            'content' => $identifier[0],
            'field' => $identifier[1],
        ];
    }
}
