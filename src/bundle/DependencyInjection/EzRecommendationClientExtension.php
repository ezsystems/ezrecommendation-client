<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use Ibexa\Contracts\PersonalizationClient\Storage\DataSourceInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class EzRecommendationClientExtension extends Extension
{
    public const DATA_SOURCE_SERVICE_TAG = 'ibexa.personalization.data_source';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('default_settings.yaml');

        $processor = new ConfigurationProcessor($container, 'ezrecommendation');
        $processor->mapConfig($config, new ConfigurationMapper());

        $container
            ->registerForAutoconfiguration(DataSourceInterface::class)
            ->addTag(self::DATA_SOURCE_SERVICE_TAG);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'ezrecommendation';
    }
}
