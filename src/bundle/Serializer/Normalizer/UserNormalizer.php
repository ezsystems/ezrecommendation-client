<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Serializer\Normalizer;

use EzSystems\EzRecommendationClient\Value\Output\User;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class UserNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    const ATTR_NAME = 'user';

    /** @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface */
    private $owningNormalizer;

    /**
     * {@inheritdoc}()
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        /** @var $object \EzSystems\EzRecommendationClient\Value\Output\User */
        return [self::ATTR_NAME => [
            '@id' => $object->getUserId(),
            'attributes' => $this->getNormalizedAttributes($object->getAttributes()),
        ]];
    }

    /**
     * {@inheritdoc}()
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof User;
    }

    /**
     * {@inheritdoc}()
     */
    public function setNormalizer(NormalizerInterface $normalizer): void
    {
        $this->owningNormalizer = $normalizer;
    }

    /**
     * Normalizes Attributes.
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    private function getNormalizedAttributes(array $attributes): array
    {
        if (empty($attributes)) {
            return [];
        }

        $attributes = $this->owningNormalizer->normalize($attributes);

        $normalizedAttributes = [];
        $normalizedAttributes['attribute'] = array_map(function ($item) {
            return $item['attribute'];
        }, $attributes);

        return $normalizedAttributes;
    }
}
