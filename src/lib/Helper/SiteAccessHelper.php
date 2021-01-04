<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Helper;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess as CurrentSiteAccess;
use EzSystems\EzRecommendationClient\Value\Parameters;
use LogicException;

/**
 * Provides utility to manipulate siteAccess.
 */
class SiteAccessHelper
{
    const SYSTEM_DEFAULT_SITEACCESS_NAME = 'default';

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    private $siteAccess;

    /** @var array */
    private $siteAccessConfig;

    /** @var string */
    private $defaultSiteAccessName;

    /**
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess
     * @param array $siteAccessConfig
     * @param string $defaultSiteAccessName
     */
    public function __construct(
        ConfigResolverInterface $configResolver,
        CurrentSiteAccess $siteAccess,
        array $siteAccessConfig,
        string $defaultSiteAccessName = self::SYSTEM_DEFAULT_SITEACCESS_NAME
    ) {
        $this->configResolver = $configResolver;
        $this->siteAccess = $siteAccess;
        $this->siteAccessConfig = $siteAccessConfig;
        $this->defaultSiteAccessName = $defaultSiteAccessName;
    }

    /**
     * Returns rootLocation by siteAccess name or by default siteAccess.
     *
     * @param string|null $siteAccessName
     *
     * @return int
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
     *
     * @return array
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
     * Returns languages based on mandatorId or siteaccess.
     *
     * @param int|null $mandatorId
     * @param string|null $siteAccess
     *
     * @return array
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function getLanguages(?int $mandatorId, ?string $siteAccess): array
    {
        if ($mandatorId) {
            $languages = $this->getMainLanguagesBySiteAccesses(
                $this->getSiteAccessesByMandatorId($mandatorId)
            );
        } elseif ($siteAccess) {
            $languages = $this->configResolver->getParameter('languages', '', $siteAccess);
        } else {
            $languages = $this->configResolver->getParameter('languages');
        }

        if (empty($languages)) {
            throw new LogicException(sprintf('No languages found using SiteAccessHelper or mandatorId'));
        }

        return $languages;
    }

    /**
     * @param int|null $mandatorId
     *
     * @return array
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function getSiteAccessesByMandatorId(?int $mandatorId): array
    {
        if ($mandatorId === null) {
            return [$this->siteAccess->name];
        }

        $siteAccesses = [];

        foreach ($this->siteAccessConfig as $siteAccessName => $config) {
            if (!isset($config['authentication']['customer_id']) || (int) $config['authentication']['customer_id'] !== $mandatorId) {
                continue;
            }

            $siteAccesses[$siteAccessName] = $siteAccessName;

            if ($this->isDefaultSiteAccessChanged()
                && $this->isSiteAccessSameAsSystemDefault($siteAccessName)
                && $this->isMandatorIdConfigured($mandatorId)
            ) {
                // default siteAccess name is changed and configuration should be adjusted
                $siteAccesses[$this->defaultSiteAccessName] = $this->defaultSiteAccessName;
            }
        }

        if (empty($siteAccesses)) {
            throw new NotFoundException('configuration for eZ Recommendation', "mandatorId: {$mandatorId}");
        }

        return array_values($siteAccesses);
    }

    /**
     * Returns siteAccesses based on mandatorId, requested siteAccess or default SiteAccessHelper.
     *
     * @param int|null $mandatorId
     * @param string|null $siteAccess
     *
     * @return array
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function getSiteAccesses(?int $mandatorId, ?string $siteAccess): array
    {
        if ($mandatorId) {
            $siteAccesses = $this->getSiteaccessesByMandatorId($mandatorId);
        } elseif ($siteAccess) {
            $siteAccesses = [$siteAccess];
        } else {
            $siteAccesses = [$this->siteAccess->name];
        }

        return $siteAccesses;
    }

    /**
     * Returns Recommendation Service credentials based on current siteAccess or mandatorId.
     *
     * @param int|null $mandatorId
     * @param string|null $siteAccess
     *
     * @return array
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function getRecommendationServiceCredentials(?int $mandatorId, ?string $siteAccess): array
    {
        $siteAccesses = $this->getSiteAccesses($mandatorId, $siteAccess);
        $siteAccess = end($siteAccesses);

        if ($siteAccess === self::SYSTEM_DEFAULT_SITEACCESS_NAME) {
            $siteAccess = null;
        }

        $customerId = $this->configResolver->getParameter('authentication.customer_id', Parameters::NAMESPACE, $siteAccess);
        $licenceKey = $this->configResolver->getParameter('authentication.license_key', Parameters::NAMESPACE, $siteAccess);

        return [$customerId, $licenceKey];
    }

    /**
     * Checks if default siteAccess is changed.
     *
     * @return bool
     */
    private function isDefaultSiteAccessChanged(): bool
    {
        return $this->defaultSiteAccessName !== self::SYSTEM_DEFAULT_SITEACCESS_NAME;
    }

    /**
     * Checks if siteAccessName is the same as system default siteAccess name.
     *
     * @param string $siteAccessName
     *
     * @return bool
     */
    private function isSiteAccessSameAsSystemDefault(string $siteAccessName): bool
    {
        return $siteAccessName === self::SYSTEM_DEFAULT_SITEACCESS_NAME;
    }

    /**
     * Checks if mandatorId is configured with default siteAccess.
     *
     * @param int $mandatorId
     *
     * @return bool
     */
    private function isMandatorIdConfigured(int $mandatorId): bool
    {
        return in_array($this->defaultSiteAccessName, $this->siteAccessConfig)
            && $this->siteAccessConfig[$this->defaultSiteAccessName]['authentication']['customer_id'] == $mandatorId;
    }

    /**
     * Returns main languages from siteAccess list.
     *
     * @param array string[] $siteAccesses
     *
     * @return array
     */
    private function getMainLanguagesBySiteAccesses(array $siteAccesses): array
    {
        $languages = [];

        foreach ($siteAccesses as $siteAccess) {
            $languageList = $this->configResolver->getParameter(
                'languages',
                '',
                $siteAccess !== 'default' ? $siteAccess : null
            );
            $mainLanguage = reset($languageList);

            if ($mainLanguage) {
                $languages[$mainLanguage] = $mainLanguage;
            }
        }

        return array_keys($languages);
    }
}
