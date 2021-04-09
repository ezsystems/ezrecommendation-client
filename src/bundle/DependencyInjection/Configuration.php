<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends SiteAccessConfiguration
{
    /**
     * Pattern using to check if relation is set according to pattern: content_type.field
     * Examples:
     *  article.image
     *  image.image
     */
    private const RELATION_PATTERN = '/^[a-zA-Z][\w]+\.[a-zA-Z][\w]+$/';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('ezrecommendation');
        $rootNode = $treeBuilder->getRootNode();

        $systemNode = $this->generateScopeBaseNode($rootNode);
        $systemNode
            ->scalarNode('site_name')
                ->info('Site name for internal usage e.g. Displaying name of Personalization limitations')
                ->example('My site name')
            ->end()
            ->arrayNode('authentication')
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
            ->arrayNode('included_content_types')
                ->info('Content types on which tracking code will be shown')
                ->example(['article', 'blog_post'])
                ->scalarPrototype()->end()
            ->end()
            ->arrayNode('random_content_types')
                ->info('Content types shown when recommendation response is empty')
                ->example(['article', 'blog_post'])
                ->scalarPrototype()->end()
            ->end()
            ->scalarNode('host_uri')
                ->info('HTTP base URI of the eZ Platform server')
                ->example('http://site.com')
            ->end()
            ->scalarNode('author_id')
                ->info('Default content author')
                ->example('14')
            ->end()
            ->arrayNode('export')
                ->children()
                    ->arrayNode('authentication')
                        ->children()
                            ->scalarNode('method')
                                ->info('Export authentication method')
                                ->example('basic / user / none')
                            ->end()
                            ->scalarNode('login')
                                ->info('Login for export authentication method')
                            ->end()
                            ->scalarNode('password')
                                ->info('Password for export authentication method')
                            ->end()
                        ->end()
                    ->end()
                    ->scalarNode('document_root')
                        ->defaultValue('%kernel.project_dir%/public/var/export/')
                    ->end()
                ->end()
            ->end()
            ->arrayNode('user_api')
                ->children()
                    ->scalarNode('default_source')
                        ->info('User API default source name')
                        ->example('source_name-en')
                    ->end()
                ->end()
            ->end()
            ->arrayNode('api')
                ->children()
                    ->arrayNode('admin')
                        ->children()
                            ->scalarNode('endpoint')
                                ->info('Admin api endpoint')
                                ->example('https://admin.net')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('recommendation')
                        ->children()
                            ->scalarNode('endpoint')
                                ->info('Recommendation api endpoint')
                                ->example('https://recommendation.net')
                            ->end()
                            ->scalarNode('consume_timeout')
                                ->info('Recommendation consume timeout')
                                ->example('20')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('event_tracking')
                        ->children()
                            ->scalarNode('endpoint')
                                ->info('Event API endpoint')
                                ->example('https://events.net')
                            ->end()
                            ->scalarNode('script_url')
                                ->info('Tracking script url')
                                ->example('cdn.yoochoose.net/yct.js')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('notifier')
                        ->children()
                            ->scalarNode('endpoint')
                                ->info('Notifier API endpoint - Should be the same as Admin API endpoint')
                                ->example('https://admin.net')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('user')
                        ->children()
                            ->scalarNode('endpoint')
                                ->info('User API endpoint')
                                ->example('https://user.net')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('field')
                ->children()
                    ->arrayNode('identifiers')
                        ->children()
                            ->arrayNode('intro')
                                ->info('Content type intro field')
                                ->example('blog_post: blog_post_intro_field')
                                ->scalarPrototype()->end()
                            ->end()
                            ->arrayNode('image')
                                ->info('Content type image field')
                                ->example('article: article_image_field')
                                ->scalarPrototype()->end()
                            ->end()
                            ->arrayNode('author')
                                ->info('Content type author field')
                                ->example('article: article_author_field')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('relations')
                        ->beforeNormalization()
                            ->ifTrue(
                                function (array $relations): bool {
                                    foreach ($relations as $relationFrom => $relationTo) {
                                        if (
                                            !preg_match(self::RELATION_PATTERN, $relationFrom)
                                            || !preg_match(self::RELATION_PATTERN, $relationTo)
                                        ) {
                                            return true;
                                        }
                                    }
                                    
                                    return false;
                                }
                            )
                            ->thenInvalid('Invalid relation %s. Relations should be configured according to pattern: "content_type.field: content_type.field"')
                            ->end()
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
