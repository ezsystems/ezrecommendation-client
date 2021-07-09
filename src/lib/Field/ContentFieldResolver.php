<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Field;

use eZ\Publish\API\Repository\Values\Content\Content;

final class ContentFieldResolver implements ContentFieldResolverInterface
{
    public function resolve(Content $content): array
    {
        $resolvedFields = [];

        foreach ($content->getFields() as $field) {
            $resolvedFields[$field->fieldDefIdentifier] = $field->value;
        }

        return $resolvedFields;
    }
}
