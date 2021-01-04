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
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigurationMapper implements HookableConfigurationMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer): void
    {
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

        if (isset($config['field'])) {
            $this->setFieldSettings($container, $config['field']);
        }

        if (isset($config['api'])) {
            $this->setApiSettings($container, $config['api']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postMap(array $config, ContextualizerInterface $contextualizer): void
    {
        // Nothing to do here.
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param array $settings
     */
    private function setApiSettings(ContainerInterface $container, array $settings): void
    {
        if ($settings) {
            foreach ($settings as $settingKey => $settingValue) {
                foreach ($settingValue as $parameterKey => $parameterValue) {
                    $container->setParameter(
                        Parameters::NAMESPACE . '.' . Parameters::API_SCOPE . '.' . $settingKey . '.' . $parameterKey,
                        $parameterValue
                    );
                }
            }
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param array $settings
     */
    private function setFieldSettings(ContainerInterface $container, array $settings)
    {
        if ($settings) {
            $parametersName = array_keys($settings);

            foreach ($parametersName as $name) {
                $container->setParameter(Parameters::NAMESPACE . '.' . Parameters::FIELD_SCOPE . '.' . $name, $settings[$name]);
            }
        }
    }
}
