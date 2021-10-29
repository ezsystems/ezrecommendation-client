<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Factory;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\API\Notifier;
use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use EzSystems\EzRecommendationClient\Exception\ExportCredentialsNotFoundException;
use EzSystems\EzRecommendationClient\Exception\MissingExportParameterException;
use EzSystems\EzRecommendationClient\Helper\SiteAccessHelper;
use EzSystems\EzRecommendationClient\SPI\ExportParametersFactoryDecorator;
use EzSystems\EzRecommendationClient\Value\ExportParameters;
use EzSystems\EzRecommendationClient\Value\Parameters;

final class ConfigurableExportParametersFactory extends ExportParametersFactoryDecorator
{
    private const REQUIRED_OPTIONS = [
        'customerId',
        'licenseKey',
        'siteaccess',
    ];

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface */
    private $credentialsResolver;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \EzSystems\EzRecommendationClient\Helper\SiteAccessHelper */
    private $siteAccessHelper;

    public function __construct(
        ExportParametersFactoryInterface $innerService,
        CredentialsResolverInterface $credentialsResolver,
        ConfigResolverInterface $configResolver,
        SiteAccessHelper $siteAccessHelper
    ) {
        $this->credentialsResolver = $credentialsResolver;
        $this->configResolver = $configResolver;
        $this->siteAccessHelper = $siteAccessHelper;

        parent::__construct($innerService);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \EzSystems\EzRecommendationClient\Exception\ExportCredentialsNotFoundException
     * @throws \EzSystems\EzRecommendationClient\Exception\MissingExportParameterException
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function create(array $properties = []): ExportParameters
    {
        $missingRequiredOptions = $this->getMissingRequiredOptions($properties);
        if (!empty($missingRequiredOptions)) {
            throw new MissingExportParameterException(sprintf(
                'Required parameters %s are missing',
                implode(', ', $missingRequiredOptions)
            ));
        }

        $properties['siteaccess'] = $properties['siteaccess'] ?? $this->getSiteAccess();

        if (!isset($properties['customerId']) && !isset($properties['licenseKey'])) {
            /** @var \EzSystems\EzRecommendationClient\Value\Config\EzRecommendationClientCredentials $credentials */
            $credentials = $this->credentialsResolver->getCredentials($properties['siteaccess']);

            if (!$this->credentialsResolver->hasCredentials($properties['siteaccess'])) {
                throw new ExportCredentialsNotFoundException(sprintf(
                   'Recommendation client export credentials are not set for siteAccess: %s',
                    $properties['siteaccess']
                ));
            }

            $properties['customerId'] = $credentials->getCustomerId();
            $properties['licenseKey'] = $credentials->getLicenseKey();
        }

        $properties['host'] = $properties['host'] ?? $this->getHostUri($properties['siteaccess']);
        $properties['webHook'] = $properties['webHook'] ?? $this->getWebHook(
                (int)$properties['customerId'],
                $properties['siteaccess']
            );

        return $this->innerService->create($properties);
    }

    private function getSiteAccess(): string
    {
        return current($this->siteAccessHelper->getSiteAccesses());
    }

    private function getHostUri(string $siteAccess): string
    {
        return $this->configResolver->getParameter(
            'host_uri',
            Parameters::NAMESPACE,
            $siteAccess
        );
    }

    private function getWebHook(int $customerId, string $siteAccess): string
    {
        return $this->configResolver->getParameter(
            'api.notifier.endpoint',
            Parameters::NAMESPACE,
            $siteAccess
        ) . sprintf(Notifier::ENDPOINT_PATH, $customerId);
    }

    /**
     * Returns missing required options
     * If one of required options has been passed to command then user must pass all options
     * If all required options are missing then options will be automatically taken from configuration.
     */
    private function getMissingRequiredOptions(array $options): array
    {
        $missingRequiredOptions = array_diff(self::REQUIRED_OPTIONS, array_keys(
            array_filter($options, static function (?string $option = null): bool {
                return null !== $option;
            })
        ));

        if (!empty(array_diff(self::REQUIRED_OPTIONS, $missingRequiredOptions))) {
            return array_intersect(self::REQUIRED_OPTIONS, $missingRequiredOptions);
        }

        return [];
    }
}
