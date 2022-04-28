<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Value\Parameters;

final class ContentTypeHelper
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @throws \Exception
     */
    public function isContentTypeExcluded(ContentType $contentType): bool
    {
        return !in_array(
            $contentType->identifier,
            $this->configResolver->getParameter('included_content_types', Parameters::NAMESPACE),
            true
        );
    }
}
