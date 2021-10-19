<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Config\ItemType;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Value\Parameters as ConfigParameters;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class IncludedItemTypeResolver implements IncludedItemTypeResolverInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const INCLUDED_ITEM_TYPES_PARAMETER = 'included_content_types';

    private ConfigResolverInterface $configResolver;

    public function __construct(
        ConfigResolverInterface $configResolver,
        ?LoggerInterface $logger = null
    ) {
        $this->configResolver = $configResolver;
        $this->logger = $logger ?? new NullLogger();
    }

    public function resolve(array $inputItemTypes, bool $useLogger, ?string $siteAccess = null): array
    {
        $includedItemTypes = $this->getConfiguredItemTypes($siteAccess);
        $notIncludedItemTypes = array_diff($inputItemTypes, $includedItemTypes);

        if ($useLogger && !empty($notIncludedItemTypes)) {
            $this->logger->warning(sprintf(
                'Item types: %s are not configured as included item types'
                . ' and have been removed from resolving criteria',
                implode(', ', $notIncludedItemTypes)
            ));
        }

        return array_intersect($includedItemTypes, $inputItemTypes);
    }

    /**
     * @return array<string>
     */
    private function getConfiguredItemTypes(?string $siteAccess = null): array
    {
        return $this->configResolver->getParameter(
            self::INCLUDED_ITEM_TYPES_PARAMETER,
            ConfigParameters::NAMESPACE,
            $siteAccess
        );
    }
}
