<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\HookableConfigurationMapperInterface;
use EzSystems\EzRecommendationClient\Value\Parameters;

class ConfigurationMapper implements HookableConfigurationMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer): void
    {
        if (isset($scopeSettings['site_name'])) {
            $contextualizer->setContextualParameter(
                'site_name',
                $currentScope,
                $scopeSettings['site_name']
            );
        }

        if (isset($scopeSettings['authentication']['customer_id'])) {
            $contextualizer->setContextualParameter('authentication.customer_id', $currentScope, $scopeSettings['authentication']['customer_id']);
        }

        if (isset($scopeSettings['authentication']['license_key'])) {
            $contextualizer->setContextualParameter('authentication.license_key', $currentScope, $scopeSettings['authentication']['license_key']);
        }

        if (isset($scopeSettings['included_content_types'])) {
            $contextualizer->setContextualParameter('included_content_types', $currentScope, $scopeSettings['included_content_types']);
        }

        if (isset($scopeSettings['random_content_types'])) {
            $contextualizer->setContextualParameter('random_content_types', $currentScope, $scopeSettings['random_content_types']);
        }

        if (isset($scopeSettings['host_uri'])) {
            $contextualizer->setContextualParameter('host_uri', $currentScope, $scopeSettings['host_uri']);
        }

        if (isset($scopeSettings['author_id'])) {
            $contextualizer->setContextualParameter('author_id', $currentScope, $scopeSettings['author_id']);
        }

        if (isset($scopeSettings['export']['authentication']['method'])) {
            $contextualizer->setContextualParameter('export.authentication.method', $currentScope, $scopeSettings['export']['authentication']['method']);
        }

        if (isset($scopeSettings['export']['authentication']['login'])) {
            $contextualizer->setContextualParameter('export.authentication.login', $currentScope, $scopeSettings['export']['authentication']['login']);
        }

        if (isset($scopeSettings['export']['authentication']['password'])) {
            $contextualizer->setContextualParameter('export.authentication.password', $currentScope, $scopeSettings['export']['authentication']['password']);
        }

        if (isset($scopeSettings['user_api']['default_source'])) {
            $contextualizer->setContextualParameter('user_api.default_source', $currentScope, $scopeSettings['user_api']['default_source']);
        }

        if (isset($scopeSettings['api'])) {
            $this->setApiSettings($contextualizer, $currentScope, $scopeSettings['api']);
        }

        if (isset($scopeSettings['field'])) {
            $this->setFieldSettings($contextualizer, $currentScope, $scopeSettings['field']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preMap(array $config, ContextualizerInterface $contextualizer): void
    {
        $container = $contextualizer->getContainer();

        if (isset($config['system'])) {
            $container->setParameter('ezrecommendation.siteaccess_config', $config['system']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postMap(array $config, ContextualizerInterface $contextualizer): void
    {
        // Nothing to do here.
    }

    private function setApiSettings(ContextualizerInterface $contextualizer, $currentScope, array $settings): void
    {
        if ($settings) {
            foreach ($settings as $settingKey => $settingValue) {
                foreach ($settingValue as $parameterKey => $parameterValue) {
                    $contextualizer->setContextualParameter(
                         Parameters::API_SCOPE . '.' . $settingKey . '.' . $parameterKey,
                        $currentScope,
                        $parameterValue
                    );
                }
            }
        }
    }

    private function setFieldSettings(ContextualizerInterface $contextualizer, $currentScope, array $settings)
    {
        if ($settings) {
            $parametersName = array_keys($settings);

            foreach ($parametersName as $name) {
                $contextualizer->setContextualParameter(
                    Parameters::FIELD_SCOPE . '.' . $name,
                    $currentScope,
                    $settings[$name]
                );
            }
        }
    }
}
