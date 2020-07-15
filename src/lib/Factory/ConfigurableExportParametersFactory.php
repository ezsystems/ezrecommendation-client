<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
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
     * {@inheritDoc}
     *
     * @throws \EzSystems\EzRecommendationClient\Exception\ExportCredentialsNotFoundException
     * @throws \EzSystems\EzRecommendationClient\Exception\MissingExportParameterException
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function create(array $properties = []): ExportParameters
    {
        if (!empty($this->getMissingRequiredOptions($properties))) {
            throw new MissingExportParameterException(sprintf(
                'Required parameters %s are missing',
                implode(', ',$this->getMissingRequiredOptions($properties))
            ));
        }

        $properties['siteaccess'] ?? $properties['siteaccess'] = $this->getSiteAccess();

        if (!isset($properties['customerId']) && !isset($properties['licenseKey'])) {
            /** @var \EzSystems\EzRecommendationClient\Value\Config\ExportCredentials $credentials */
            $credentials = $this->credentialsResolver->getCredentials($properties['siteaccess']);

            if (!$this->credentialsResolver->hasCredentials()) {
                throw new ExportCredentialsNotFoundException(sprintf(
                   'Recommendation client export credentials are not set for siteAccess: %s',
                    $properties['siteaccess']
                ));
            }

            $properties['customerId'] = $credentials->getLogin();
            $properties['licenseKey'] = $credentials->getPassword();
        }

        $properties['host'] ?? $properties['host'] = $this->getHostUri($properties['siteaccess']);
        $properties['webHook'] ?? $properties['webHook'] = $this->getWebHook(
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

    private function getMissingRequiredOptions(array $options): array
    {
        $missingOptions = [];

        if (isset($options['customerId'])) {
            if (!isset($options['licenseKey'])) {
                $missingOptions[] = 'licenseKey';
            }

            if (!isset($options['siteaccess'])) {
                $missingOptions[] = 'siteaccess';
            }
        }

        return $missingOptions;
    }
}
