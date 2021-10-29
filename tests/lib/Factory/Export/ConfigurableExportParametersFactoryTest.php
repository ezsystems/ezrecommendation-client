<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\PersonalizationClient\Factory\Export;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use EzSystems\EzRecommendationClient\Exception\ExportCredentialsNotFoundException;
use EzSystems\EzRecommendationClient\Exception\MissingExportParameterException;
use EzSystems\EzRecommendationClient\Factory\ConfigurableExportParametersFactory;
use EzSystems\EzRecommendationClient\Factory\ExportParametersFactoryInterface;
use EzSystems\EzRecommendationClient\Helper\SiteAccessHelper;
use EzSystems\EzRecommendationClient\Value\Config\EzRecommendationClientCredentials;
use EzSystems\EzRecommendationClient\Value\ExportParameters;
use PHPUnit\Framework\TestCase;

/**
 * @covers \EzSystems\EzRecommendationClient\Factory\ConfigurableExportParametersFactory
 */
final class ConfigurableExportParametersFactoryTest extends TestCase
{
    /** @var \EzSystems\EzRecommendationClient\Factory\ConfigurableExportParametersFactory */
    private $parametersFactory;

    /** @var \EzSystems\EzRecommendationClient\Factory\ExportParametersFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $innerParametersFactory;

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $credentialsResolver;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

    /** @var \EzSystems\EzRecommendationClient\Helper\SiteAccessHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $siteAccessHelper;

    /**
     * @phpstan-var array{
     *  customerId: string,
     *  licenseKey: string,
     *  contentTypeIdList: string,
     *  languages: string,
     *  siteaccess: string,
     *  webHook: string,
     *  host: string,
     *  pageSize: string,
     * }
     */
    private $options;

    public function setUp(): void
    {
        $this->innerParametersFactory = $this->createMock(ExportParametersFactoryInterface::class);
        $this->credentialsResolver = $this->createMock(CredentialsResolverInterface::class);
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->siteAccessHelper = $this->createMock(SiteAccessHelper::class);
        $this->parametersFactory = new ConfigurableExportParametersFactory(
            $this->innerParametersFactory,
            $this->credentialsResolver,
            $this->configResolver,
            $this->siteAccessHelper,
        );
        $this->options = [
            'customerId' => '12345',
            'licenseKey' => '12345-12345-12345-12345',
            'siteaccess' => 'test',
            'contentTypeIdList' => 'article, product, blog',
            'languages' => 'eng-GB',
            'webHook' => 'https://reco-engine.com/api/12345/items',
            'host' => 'https://127.0.0.1',
            'pageSize' => '500',
        ];
    }

    public function testCreateFromAllOptions(): void
    {
        $exportParameters = new ExportParameters($this->options);

        $this->configureInnerParameterFactoryServiceToReturnExportCredentials($exportParameters);

        self::assertEquals(
            $exportParameters,
            $this->parametersFactory->create($this->options)
        );
    }

    public function testCreateWithAutocomplete(): void
    {
        $exportParameters = new ExportParameters($this->options);
        $siteAccess = 'test';
        $options = [
            'customerId' => null,
            'licenseKey' => null,
            'siteaccess' => null,
            'contentTypeIdList' => 'article, product, blog',
            'languages' => 'eng-GB',
            'pageSize' => '500',
            'webHook' => null,
            'host' => null,
        ];

        $this->configureSiteAccessHelperToReturnSiteAccessName($siteAccess);
        $this->configureCredentialsResolverToReturnIfCredentialsAreConfiguredForSiteAccess($siteAccess, true);
        $this->configureCredentialsResolverToReturnRecommendationClientCredentials(
            $siteAccess,
            12345,
            '12345-12345-12345-12345'
        );
        $this->configureConfigResolverToReturnHostUriAndApiNotifierUri($siteAccess);
        $this->configureInnerParameterFactoryServiceToReturnExportCredentials($exportParameters);

        self::assertEquals(
            $exportParameters,
            $this->parametersFactory->create($options)
        );
    }

    /**
     * @param array<string, string|int> $parameters
     * @param array<string> $missingParameters
     *
     * @dataProvider provideDataForTestThrowMissingExportParameterException
     */
    public function testThrowMissingExportParameterException(
        array $parameters,
        array $missingParameters
    ): void {
        $this->expectException(MissingExportParameterException::class);
        $this->expectExceptionMessage(
            sprintf('Required parameters %s are missing', implode(', ', $missingParameters))
        );

        $this->parametersFactory->create($parameters);
    }

    public function testThrowExportCredentialsNotFoundException(): void
    {
        $siteAccess = 'foo';

        $this->configureSiteAccessHelperToReturnSiteAccessName($siteAccess);
        $this->configureCredentialsResolverToReturnIfCredentialsAreConfiguredForSiteAccess(
            $siteAccess,
            false
        );

        $this->expectException(ExportCredentialsNotFoundException::class);
        $this->expectExceptionMessage('Recommendation client export credentials are not set for siteAccess: foo');

        $this->parametersFactory->create(
            [
                'customerId' => null,
                'licenseKey' => null,
                'siteaccess' => null,
            ]
        );
    }

    /**
     * @phpstan-return iterable<array{
     *  array<string, string|int>,
     *  array<string>
     * }>
     */
    public function provideDataForTestThrowMissingExportParameterException(): iterable
    {
        yield [
            [
                'siteaccess' => 'site',
            ],
            [
                'customerId',
                'licenseKey',
            ],
        ];

        yield [
            [
                'customerId' => 12345,
            ],
            [
                'licenseKey',
                'siteaccess',
            ],
        ];

        yield [
            [
                'licenseKey' => '12345-12345-12345-12345',
            ],
            [
                'customerId',
                'siteaccess',
            ],
        ];

        yield [
            [
                'siteaccess' => 'site',
                'customerId' => 12345,
            ],
            [
                'licenseKey',
            ],
        ];

        yield [
            [
                'siteaccess' => 'site',
                'licenseKey' => '12345-12345-12345-12345',
            ],
            [
                'customerId',
            ],
        ];

        yield [
            [
                'customerId' => 12345,
                'licenseKey' => '12345-12345-12345-12345',
            ],
            [
                'siteaccess',
            ],
        ];
    }

    private function configureCredentialsResolverToReturnIfCredentialsAreConfiguredForSiteAccess(
        string $siteAccess,
        bool $hasCredentials
    ): void {
        $this->credentialsResolver
            ->expects(self::atLeastOnce())
            ->method('hasCredentials')
            ->with($siteAccess)
            ->willReturn($hasCredentials);
    }

    private function configureCredentialsResolverToReturnRecommendationClientCredentials(
        string $siteAccess,
        int $customerId,
        string $licenseKey
    ): void {
        $this->credentialsResolver
            ->expects(self::once())
            ->method('getCredentials')
            ->with($siteAccess)
            ->willReturn(
                new EzRecommendationClientCredentials(
                    [
                        'customerId' => $customerId,
                        'licenseKey' => $licenseKey,
                    ]
                )
            );
    }

    private function configureConfigResolverToReturnHostUriAndApiNotifierUri(string $siteAccess): void
    {
        $this->configResolver
            ->expects(self::atLeastOnce())
            ->method('getParameter')
            ->willReturnMap(
                [
                    ['host_uri', 'ezrecommendation', $siteAccess, 'https://127.0.0.1'],
                    ['api.notifier.endpoint', 'ezrecommendation', $siteAccess, 'https://reco-engine.com'],
                ]
            );
    }

    private function configureSiteAccessHelperToReturnSiteAccessName(string $siteAccess): void
    {
        $helper = $this->siteAccessHelper;

        /** @var \PHPUnit\Framework\MockObject\MockObject $helper */
        $helper
            ->expects(self::atLeastOnce())
            ->method('getSiteAccesses')
            ->willReturn(
                [$siteAccess]
            );
    }

    private function configureInnerParameterFactoryServiceToReturnExportCredentials(
        ExportParameters $exportParameters
    ): void {
        $this->innerParametersFactory
            ->expects(self::once())
            ->method('create')
            ->with($this->options)
            ->willReturn($exportParameters);
    }
}
