<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Serializer\Normalizer;

use EzSystems\EzRecommendationClient\Value\Output\Attribute;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizer implements NormalizerInterface
{
    const ATTR_NAME = 'attribute';

    /**
     * @inheritDoc()
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        /** @var $object \EzSystems\EzRecommendationClient\Value\Output\Attribute */
        return [self::ATTR_NAME => [
            '@key' => $object->getName(),
            '@value' => $object->getValue(),
            '@type' => $object->getType(),
        ]];
    }

    /**
     * @inheritDoc()
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Attribute;
    }
}
