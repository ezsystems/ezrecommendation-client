<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Personalization;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use eZ\Bundle\EzPublishCoreBundle\EzPublishCoreBundle;
use eZ\Bundle\EzPublishLegacySearchEngineBundle\EzPublishLegacySearchEngineBundle;
use eZ\Publish\API\Repository\Tests\LegacySchemaImporter;
use eZ\Publish\SPI\Tests\Persistence\FixtureImporter;
use EzSystems\DoctrineSchema\Database\DbPlatform\SqliteDbPlatform;
use EzSystems\EzPlatformCoreBundle\EzPlatformCoreBundle;
use EzSystems\EzPlatformRestBundle\EzPlatformRestBundle;
use EzSystems\EzPlatformRichTextBundle\EzPlatformRichTextBundle;
use EzSystems\EzRecommendationClientBundle\EzRecommendationClientBundle;
use FOS\JsRoutingBundle\FOSJsRoutingBundle;
use Hautelook\TemplatedUriBundle\HautelookTemplatedUriBundle;
use JMS\TranslationBundle\JMSTranslationBundle;
use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use Liip\ImagineBundle\LiipImagineBundle;
use Psr\Log\Test\TestLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;

final class IbexaTestKernel extends Kernel
{
    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        return [
            new DoctrineBundle(),
            new EzRecommendationClientBundle(),
            new EzPlatformCoreBundle(),
            new EzPlatformRestBundle(),
            new EzPlatformRichTextBundle(),
            new EzPublishCoreBundle(),
            new EzPublishLegacySearchEngineBundle(),
            new FOSJsRoutingBundle(),
            new FrameworkBundle(),
            new HautelookTemplatedUriBundle(),
            new JMSTranslationBundle(),
            new LexikJWTAuthenticationBundle(),
            new LiipImagineBundle(),
            new SecurityBundle(),
            new TwigBundle(),
        ];
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(static function (ContainerBuilder $container): void {
            self::prepareIbexaDXP($container);
            self::prepareDatabaseConnection($container);
            self::prepareRecommendationConfiguration($container);
            self::prepareJWTAuthenticationConfiguration($container);
            self::disableLogger($container);
        });
    }

    private static function prepareIbexaDXP(ContainerBuilder $container): void
    {
        $container->setParameter('io_root_dir', '');
        $container->setParameter('kernel.secret', 'secret');
        $container->loadFromExtension('ezplatform', [
            'siteaccess' => [
                'default_siteaccess' => 'first_siteaccess',
                'list' => ['first_siteaccess', 'second_siteaccess'],
                'match' => null,
            ],
            'repositories' => [
                'default' => [
                    'storage' => null,
                    'search' => [
                        'engine' => 'legacy',
                        'connection' => 'default',
                    ],
                ],
            ],
        ]);

        $container->loadFromExtension('security', [
            'providers' => [
                'default' => [
                    'id' => 'ezpublish.security.user_provider',
                ],
            ],
            'firewalls' => [
                'main' => [
                    'anonymous' => null,
                ],
            ],
        ]);

        $container->loadFromExtension('framework', [
            'test' => true,
            'session' => [
                'storage_id' => 'session.storage.mock_file',
            ],
            'cache' => [
                'app' => 'cache.adapter.array',
            ],
            'router' => [
                'resource' => 'foo',
            ],
        ]);

        $container->setAlias(
            'eZ\Publish\Core\MVC\ConfigResolverInterface',
            'eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ChainConfigResolver'
        );
    }

    private static function prepareDatabaseConnection(ContainerBuilder $container): void
    {
        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'memory' => true,
                'driver' => 'pdo_sqlite',
                'platform_service' => SqliteDbPlatform::class,
                'logging' => false,
            ],
        ]);

        $definition = new Definition(LegacySchemaImporter::class);
        $definition->setPublic(true);
        $definition->setArgument(0, new Reference('doctrine.dbal.default_connection'));
        $container->setDefinition('test.ibexa.personalization.schema_importer', $definition);

        $definition = new Definition(FixtureImporter::class);
        $definition->setPublic(true);
        $definition->setArgument(0, new Reference('doctrine.dbal.default_connection'));
        $container->setDefinition('test.ibexa.personalization.fixture_importer', $definition);

        $definition = new Definition(SqliteDbPlatform::class);
        $definition->addMethodCall('setEventManager', [
            new Reference('doctrine.dbal.default_connection.event_manager'),
        ]);
        $container->setDefinition(SqliteDbPlatform::class, $definition);
    }

    private static function prepareRecommendationConfiguration(ContainerBuilder $container): void
    {
        $container->loadFromExtension('ezrecommendation', [
            'system' => [
                'default' => [
                    'api' => [
                        'admin' => [
                            'endpoint' => 'fake.endpoint.com',
                        ],
                        'recommendation' => [
                            'endpoint' => 'fake.endpoint.com',
                        ],
                        'event_tracking' => [
                            'endpoint' => 'fake.endpoint.com',
                        ],
                    ],
                ],
                'first_siteaccess' => [
                    'authentication' => [
                        'customer_id' => 12345,
                        'license_key' => '12345-12345-12345-12345',
                    ],
                    'export' => [
                        'authentication' => [
                            'method' => 'basic',
                            'login' => '12345',
                            'password' => '12345-12345-12345-12345',
                        ],
                    ],
                    'host_uri' => 'https://127.0.0.1/',
                ],
                'second_siteaccess' => [
                    'authentication' => [
                        'customer_id' => 56789,
                        'license_key' => '56789-56789-56789-56789',
                    ],
                    'export' => [
                        'authentication' => [
                            'method' => 'basic',
                            'login' => '56789',
                            'password' => '56789-56789-56789-56789',
                        ],
                    ],
                    'host_uri' => 'https://127.0.0.1/',
                ],
            ],
        ]);
    }

    private static function prepareJWTAuthenticationConfiguration(ContainerBuilder $container): void
    {
        $container->loadFromExtension('lexik_jwt_authentication', [
            'secret_key' => 'private.pem',
            'pass_phrase' => 'testing',
        ]);
    }

    private static function disableLogger(ContainerBuilder $container): void
    {
        $container->setDefinition('logger', new Definition(TestLogger::class));
    }
}
