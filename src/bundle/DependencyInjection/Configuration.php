<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;

class Configuration extends SiteAccessConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ez_recommendation');

        $systemNode = $this->generateScopeBaseNode($rootNode);
        $systemNode
            ->arrayNode('recommendation')
                ->children()
                    ->scalarNode('customer_id')
                        ->info('Recommendation customer ID')
                        ->example('12345')
                        ->isRequired()
                    ->end()
                    ->scalarNode('license_key')
                        ->info('Recommendation license key')
                        ->example('1234-5678-9012-3456-7890')
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('recommendation')
                ->children()
                    ->arrayNode('included_content_types')
                        ->info('Content types on which tracking code will be shown')
                        ->example(['article', 'blog_post'])
                        ->prototype('scalar')->end()
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
            ->scalarNode('server_uri')
                ->info('HTTP base URI of the eZ Publish server')
                ->example('http://site.com')
                ->isRequired()
            ->end();

        $rootNode
            ->children()
                ->arrayNode('tracking')
                    ->children()
                        ->scalarNode('api_endpoint')
                            ->info('Recommendation tracking end-point URI address')
                            ->example('http://event.yoochoose.net')
                        ->end()
                        ->scalarNode('script_url')
                            ->info('Recommendation tracking script address')
                            ->example('cdn.yoochoose.net/yct.js')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('recommendation')
                    ->children()
                        ->scalarNode('api_endpoint')
                            ->info('Recommendation end-point URI address')
                            ->example('http://event.yoochoose.net')
                        ->end()
                        ->scalarNode('consume_timeout')
                            ->info('Describes when `consume` event should be submitted after page loading (in seconds)')
                            ->example('20')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('api_endpoint')
                    ->info('Recommendation end-point URI address')
                    ->example('https://admin.yoochoose.net')
                ->end()
                ->arrayNode('export')
                    ->children()
                        ->scalarNode('document_root')
                            ->defaultValue('%kernel.root_dir%/../web/var/export/')
                        ->end()
                        ->arrayNode('users_authentication')
                            ->children()
                                ->scalarNode('method')
                                    ->info('Export authentication method')
                                    ->example('basic / user / none')
                                ->end()
                                ->scalarNode('login')
                                    ->info('LoginListener for export authentication method')
                                ->end()
                                ->scalarNode('password')
                                    ->info('Password for export authentication method')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
