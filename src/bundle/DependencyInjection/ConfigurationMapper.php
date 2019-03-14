<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\HookableConfigurationMapperInterface;

class ConfigurationMapper implements HookableConfigurationMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        // Common settings
        if (isset($scopeSettings['server_uri'])) {
            $contextualizer->setContextualParameter('server_uri', $currentScope, $scopeSettings['server_uri']);
        }

        if (isset($scopeSettings['recommendation']['customer_id'])) {
            $contextualizer->setContextualParameter('recommendation.customer_id', $currentScope, $scopeSettings['recommendation']['customer_id']);
        }
        if (isset($scopeSettings['recommendation']['license_key'])) {
            $contextualizer->setContextualParameter('recommendation.license_key', $currentScope, $scopeSettings['recommendation']['license_key']);
        }

        if (isset($scopeSettings['recommendation']['included_content_types'])) {
            $contextualizer->setContextualParameter('recommendation.included_content_types', $currentScope, $scopeSettings['recommendation']['included_content_types']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preMap(array $config, ContextualizerInterface $contextualizer)
    {
        // Nothing to do here.
    }

    /**
     * {@inheritdoc}
     */
    public function postMap(array $config, ContextualizerInterface $contextualizer)
    {
        // Nothing to do here.
    }
}
