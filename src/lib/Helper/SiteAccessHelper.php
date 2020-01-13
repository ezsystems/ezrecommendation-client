<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess as CurrentSiteAccess;

/**
 * Provides utility to manipulate siteAccess.
 */
final class SiteAccessHelper
{
    const SYSTEM_DEFAULT_SITE_ACCESS_NAME = 'default';

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    private $siteAccess;

    /** @var array */
    private $siteAccessConfig;

    /** @var string */
    private $defaultSiteAccessName;

    public function __construct(
        ConfigResolverInterface $configResolver,
        CurrentSiteAccess $siteAccess,
        array $siteAccessConfig,
        string $defaultSiteAccessName = self::SYSTEM_DEFAULT_SITE_ACCESS_NAME
    ) {
        $this->configResolver = $configResolver;
        $this->siteAccess = $siteAccess;
        $this->siteAccessConfig = $siteAccessConfig;
        $this->defaultSiteAccessName = $defaultSiteAccessName;
    }

    /**
     * Returns rootLocation by siteAccess name or by default siteAccess.
     */
    public function getRootLocationBySiteAccessName(?string $siteAccessName): int
    {
        return $this->configResolver->getParameter(
            'content.tree_root.location_id',
            null,
            $siteAccessName ?: $this->siteAccess->name
        );
    }

    /**
     * Returns list of rootLocations from siteAccess list.
     *
     * @param string[] $siteAccesses
     */
    public function getRootLocationsBySiteAccesses(array $siteAccesses): array
    {
        $rootLocations = [];

        foreach ($siteAccesses as $siteAccess) {
            $rootLocationId = $this->getRootLocationBySiteAccessName($siteAccess);
            $rootLocations[$rootLocationId] = $rootLocationId;
        }

        return array_keys($rootLocations);
    }

    /**
     * Returns languages based on customerId or siteaccess.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function getLanguages(int $customerId, ?string $siteAccess): array
    {
        if (!$siteAccess) {
            return $this->getLanguagesBySiteAccesses(
                $this->getSiteAccessesByCustomerId($customerId)
            );
        } else {
            return $this->getLanguageList($siteAccess);
        }
    }

    /**
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function getSiteAccessesByCustomerId(?int $customerId): array
    {
        if ($customerId === null) {
            return [$this->siteAccess->name];
        }

        $siteAccesses = [];

        foreach ($this->siteAccessConfig as $siteAccessName => $config) {
            if (!isset($config['authentication']['customer_id']) || (int)$config['authentication']['customer_id'] !== $customerId) {
                continue;
            }

            $siteAccesses[$siteAccessName] = $siteAccessName;

            if ($this->isDefaultSiteAccessChanged()
                && $this->isSiteAccessSameAsSystemDefault($siteAccessName)
                && $this->isCustomerIdConfigured($customerId)
            ) {
                // default siteAccess name is changed and configuration should be adjusted
                $siteAccesses[$this->defaultSiteAccessName] = $this->defaultSiteAccessName;
            }
        }

        if (empty($siteAccesses)) {
            throw new NotFoundException('configuration for eZ Recommendation', "customerId: {$customerId}");
        }

        return array_values($siteAccesses);
    }

    /**
     * Returns siteAccesses based on customerId, requested siteAccess or default SiteAccessHelper.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function getSiteAccesses(?int $customerId, ?string $siteAccess): array
    {
        if ($customerId) {
            $siteAccesses = $this->getSiteaccessesByCustomerId($customerId);
        } elseif ($siteAccess) {
            $siteAccesses = [$siteAccess];
        } else {
            $siteAccesses = [$this->siteAccess->name];
        }

        return $siteAccesses;
    }

    /**
     * Returns languages from siteAccess list.
     */
    public function getLanguagesBySiteAccesses(array $siteAccesses): array
    {
        if (\count($siteAccesses) === 1 && $this->isSiteAccessSameAsSystemDefault(current($siteAccesses))) {
            return $this->getLanguageList();
        }

        return $this->getMainLanguagesBySiteAccesses($siteAccesses);
    }

    /**
     * Returns main languages from siteAccess list.
     */
    public function getMainLanguagesBySiteAccesses(array $siteAccesses): array
    {
        $languages = [];

        foreach ($siteAccesses as $siteAccess) {
            $language = current($this->getLanguageList(
                !$this->isSiteAccessSameAsSystemDefault($siteAccess) ? $siteAccess : null)
            );

            if ($language) {
                $languages[$language] = $language;
            }
        }

        return array_keys($languages);
    }

    /**
     * Gets LanguageList for given siteAccess using ConfigResolver.
     */
    public function getLanguageList(?string $siteAccess = null): array
    {
        return $this->configResolver->getParameter('languages', null, $siteAccess);
    }

    /**
     * Checks if default siteAccess is changed.
     */
    public function isDefaultSiteAccessChanged(): bool
    {
        return $this->defaultSiteAccessName !== self::SYSTEM_DEFAULT_SITE_ACCESS_NAME;
    }

    /**
     * Checks if siteAccessName is the same as system default siteAccess name.
     */
    public function isSiteAccessSameAsSystemDefault(string $siteAccessName): bool
    {
        return $siteAccessName === self::SYSTEM_DEFAULT_SITE_ACCESS_NAME;
    }

    /**
     * Checks if customerId is configured with default siteAccess.
     */
    private function isCustomerIdConfigured(int $customerId): bool
    {
        return \in_array($this->defaultSiteAccessName, $this->siteAccessConfig)
            && $this->siteAccessConfig[$this->defaultSiteAccessName]['authentication']['customer_id'] == $customerId;
    }
}
