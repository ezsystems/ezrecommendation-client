<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\SPI\Variation\VariationHandler as ImageVariationServiceInterface;

class ImageHelper
{
    /** @var \eZ\Publish\SPI\Variation\VariationHandler */
    private $imageVariationService;

    /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver */
    private $configResolver;

    /**
     * @param \eZ\Publish\SPI\Variation\VariationHandler $imageVariation
     * @param \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver $configResolver
     */
    public function __construct(
        ImageVariationServiceInterface $imageVariationService,
        ConfigResolverInterface $configResolver
    ) {
        $this->imageVariationService = $imageVariationService;
        $this->configResolver = $configResolver;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param array|null $options
     *
     * @return string
     */
    public function getImageUrl(Field $field, Content $content, ?array $options = null): string
    {
        $variations = $this->configResolver->getParameter('image_variations');
        $variation = 'original';

        if ((!empty($options['image'])) && in_array($options['image'], array_keys($variations))) {
            $variation = $options['image'];
        }

        $uri = $this
            ->imageVariationService
            ->getVariation($field, $content->versionInfo, $variation)
            ->uri;

        if (strpos($uri, 'http://:0') !== false) {
            $uri = str_replace('http://:0', 'http://0', $uri);
        }

        return parse_url($uri, PHP_URL_PATH);
    }
}
