<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Factory\Export;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface;
use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use EzSystems\EzRecommendationClient\Exception\InvalidArgumentException;
use EzSystems\EzRecommendationClient\Value\Config\EzRecommendationClientCredentials;
use Ibexa\Personalization\Factory\Export\ParametersFactory;
use Ibexa\Personalization\Factory\Export\ParametersFactoryInterface;
use Ibexa\Personalization\Value\Export\Parameters;
use PHPUnit\Framework\TestCase;

final class ParametersFactoryTest extends TestCase
{
    private ParametersFactoryInterface $parametersFactory;

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private CredentialsResolverInterface $credentialsResolver;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private ConfigResolverInterface $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface|\PHPUnit\Framework\MockObject\MockObject */
    private SiteAccessServiceInterface $siteAccessService;

    /**
     * @phpstan-var array{
     *  customer_id: string,
     *  license_key: string,
     *  item_type_identifier_list: string,
     *  languages: string,
     *  siteaccess: string,
     *  web_hook: string,
     *  host: string,
     *  page_size: string,
     * }
     */
    private array $options;

    public function setUp(): void
    {
        $this->credentialsResolver = $this->createMock(CredentialsResolverInterface::class);
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->siteAccessService = $this->createMock(SiteAccessServiceInterface::class);
        $this->parametersFactory = new ParametersFactory(
            $this->credentialsResolver,
            $this->configResolver,
            $this->siteAccessService,
        );
        $this->options = [
            'customer_id' => '12345',
            'license_key' => '12345-12345-12345-12345',
            'siteaccess' => 'test',
            'item_type_identifier_list' => 'article, product, blog',
            'languages' => 'eng-GB',
            'web_hook' => 'https://reco-engine.com/api/12345/items',
            'host' => 'https://127.0.0.1',
            'page_size' => '500',
        ];
    }

    /**
     * @throws \EzSystems\EzRecommendationClient\Exception\MissingExportParameterException
     */
    public function testCreateFromAllOptions(): void
    {
        $this->configureSiteAccessServiceToReturnAllSiteAccesses();

        self::assertEquals(
            Parameters::fromArray($this->options),
            $this->parametersFactory->create($this->options, ParametersFactoryInterface::COMMAND_TYPE)
        );
    }

    public function testCreateWithAutocomplete(): void
    {
        $siteAccess = 'test';
        $options = [
            'customer_id' => null,
            'license_key' => null,
            'siteaccess' => null,
            'item_type_identifier_list' => 'article, product, blog',
            'languages' => 'eng-GB',
            'page_size' => '500',
            'web_hook' => null,
            'host' => null,
        ];

        $this->credentialsResolver
            ->expects(self::atLeastOnce())
            ->method('hasCredentials')
            ->with($siteAccess)
            ->willReturn(true);

        $this->siteAccessService
            ->expects(self::atLeastOnce())
            ->method('getAll')
            ->willReturn(
                [
                    new SiteAccess($siteAccess),
                ]
            );

        $this->credentialsResolver
            ->expects(self::once())
            ->method('getCredentials')
            ->with($siteAccess)
            ->willReturn(new EzRecommendationClientCredentials(
                12345,
                '12345-12345-12345-12345'
            ));

        $this->configureConfigResolverToReturnHostUriAndApiNotifierUri($siteAccess);

        self::assertEquals(
            Parameters::fromArray($this->options),
            $this->parametersFactory->create($options, ParametersFactoryInterface::COMMAND_TYPE)
        );
    }

    public function testCreateForSingleConfiguration(): void
    {
        $siteAccess = 'test';
        $options = [
            'customer_id' => '12345',
            'license_key' => '12345-12345-12345-12345',
            'siteaccess' => $siteAccess,
            'item_type_identifier_list' => 'article, product, blog',
            'languages' => 'eng-GB',
            'page_size' => '500',
            'web_hook' => null,
            'host' => null,
        ];

        $this->siteAccessService
            ->method('getAll')
            ->willReturn(
                [
                    new SiteAccess($siteAccess),
                ]
            );

        $this->credentialsResolver
            ->expects(self::once())
            ->method('hasCredentials')
            ->with($siteAccess)
            ->willReturn(true);

        $this->configureConfigResolverToReturnHostUriAndApiNotifierUri($siteAccess);

        self::assertEquals(
            Parameters::fromArray($this->options),
            $this->parametersFactory->create($options, ParametersFactoryInterface::COMMAND_TYPE)
        );
    }

    public function testCreateForMultiCustomerConfiguration(): void
    {
        $firstSiteAccess = 'test';
        $secondSiteAccess = 'second_siteaccess';

        $options = [
            'customer_id' => '12345',
            'license_key' => '12345-12345-12345-12345',
            'siteaccess' => $firstSiteAccess,
            'item_type_identifier_list' => 'article, product, blog',
            'languages' => 'eng-GB',
            'page_size' => '500',
            'web_hook' => null,
            'host' => null,
        ];

        $this->configureSiteAccessServiceToReturnAllSiteAccesses();

        $this->credentialsResolver
            ->expects(self::at(0))
            ->method('hasCredentials')
            ->with($firstSiteAccess)
            ->willReturn(true);

        $this->credentialsResolver
            ->expects(self::at(1))
            ->method('hasCredentials')
            ->with($secondSiteAccess)
            ->willReturn(true);

        $this->configureConfigResolverToReturnHostUriAndApiNotifierUri($firstSiteAccess);

        self::assertEquals(
            Parameters::fromArray($this->options),
            $this->parametersFactory->create($options, ParametersFactoryInterface::COMMAND_TYPE)
        );
    }

    /**
     * @throws \EzSystems\EzRecommendationClient\Exception\MissingExportParameterException
     */
    public function testThrowExportCredentialsNotFoundException(): void
    {
        $siteAccess = 'invalid';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('SiteAccess %s doesn\'t exists', $siteAccess));

        $this->configureSiteAccessServiceToReturnAllSiteAccesses();

        $this->parametersFactory->create(
            [
                'customer_id' => null,
                'license_key' => null,
                'siteaccess' => $siteAccess,
                'item_type_identifier_list' => 'article, product, blog',
                'languages' => 'eng-GB',
                'page_size' => '500',
                'web_hook' => null,
                'host' => null,
            ],
            ParametersFactoryInterface::COMMAND_TYPE
        );
    }

    private function configureSiteAccessServiceToReturnAllSiteAccesses(): void
    {
        $this->siteAccessService
            ->method('getAll')
            ->willReturn(
                [
                    new SiteAccess('test'),
                    new SiteAccess('second_siteaccess'),
                ]
            );
    }

    private function configureConfigResolverToReturnHostUriAndApiNotifierUri(string $siteAccess): void
    {
        $this->configResolver
            ->expects(self::atLeastOnce())
            ->method('getParameter')
            ->willReturn(self::returnValueMap(
                [
                    ['host_uri', 'ezrecommendation', $siteAccess, 'https://127.0.0.1'],
                    ['api.notifier.endpoint', 'ezrecommendation', $siteAccess, 'https://reco-engine.com'],
                ]
            ));
    }
}
