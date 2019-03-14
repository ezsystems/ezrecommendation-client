<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class EzRecommendationClientExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('services/slots.yml');
        $loader->load('default_settings.yml');

        if (isset($config['api_endpoint'])) {
            $container->setParameter('ez_recommendation.api_endpoint', $config['api_endpoint']);
        }

        if (isset($config['recommendation']['api_endpoint'])) {
            $container->setParameter('ez_recommendation.recommendation.api_endpoint', $config['recommendation']['api_endpoint']);
        }

        if (isset($config['recommendation']['consume_timeout'])) {
            $container->setParameter('ez_recommendation.recommendation.consume_timeout', $config['recommendation']['consume_timeout']);
        }

        if (isset($config['tracking']['script_url'])) {
            $container->setParameter('ez_recommendation.tracking.script_url', $config['tracking']['script_url']);
        }

        if (isset($config['tracking']['api_endpoint'])) {
            $container->setParameter('ez_recommendation.tracking.api_endpoint', $config['tracking']['api_endpoint']);
        }

        if (isset($config['system'])) {
            $container->setParameter('ez_recommendation.siteaccess_config', $config['system']);
        }

        if (isset($config['export']['document_root'])) {
            $container->setParameter(
                'ez_recommendation.export.document_root',
                $config['export']['document_root']
            );
        }

        if (isset($config['export']['users_authentication']['method'])) {
            $container->setParameter(
                'ez_recommendation.export.users_authentication.method',
                $config['export']['users_authentication']['method']
            );
        }

        if (isset($config['export']['users_authentication']['login'])) {
            $container->setParameter(
                'ez_recommendation.export.users_authentication.login',
                $config['export']['users_authentication']['login']
            );
        }

        if (isset($config['export']['users_authentication']['password'])) {
            $container->setParameter(
                'ez_recommendation.export.users_authentication.password',
                $config['export']['users_authentication']['password']
            );
        }

        $processor = new ConfigurationProcessor($container, 'ez_recommendation');
        $processor->mapConfig($config, new ConfigurationMapper());
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'ez_recommendation';
    }
}
