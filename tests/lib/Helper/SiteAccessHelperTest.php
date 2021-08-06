<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Helper;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface;
use EzSystems\EzRecommendationClient\Helper\SiteAccessHelper;
use PHPUnit\Framework\TestCase;

class SiteAccessHelperTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface|\PHPUnit\Framework\MockObject\MockObject */
    private SiteAccessServiceInterface $siteAccessService;

    public function setUp(): void
    {
        parent::setUp();

        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->siteAccessService = $this->createMock(SiteAccessServiceInterface::class);
    }

    public function testGetRootLocationBySiteAccessNameWithoutParameterSpecified()
    {
        $this->siteAccessService
            ->expects(self::atLeastOnce())
            ->method('getCurrent')
            ->willReturn(new SiteAccess('foo', 'test'));

        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('content.tree_root.location_id'))
            ->willReturn(123);

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            [],
            'default'
        );

        $result = $siteAccessHelper->getRootLocationBySiteAccessName(null);

        $this->assertEquals(123, $result);
    }

    public function testGetRootLocationBySiteAccessName()
    {
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('content.tree_root.location_id'), null, 'foo')
            ->willReturn(123);

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            [],
            'default'
        );

        $result = $siteAccessHelper->getRootLocationBySiteAccessName('foo');

        $this->assertEquals(123, $result);
    }

    public function testGetRootLocationsBySiteAccesses()
    {
        $siteAccesses = [
            'abc',
            'cde',
        ];

        $this->configResolver
            ->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('content.tree_root.location_id'), null, 'abc')
            ->willReturn(1);

        $this->configResolver
            ->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('content.tree_root.location_id'), null, 'cde')
            ->willReturn(2);

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            [],
            'default'
        );

        $result = $siteAccessHelper->getRootLocationsBySiteAccesses($siteAccesses);

        $this->assertEquals([1, 2], $result);
    }

    public function testGetLanguageList()
    {
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('languages'))
            ->willReturn(['eng-GB', 'fre-FR']);

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            [],
            'default'
        );

        $result = $siteAccessHelper->getLanguageList(null);

        $this->assertEquals(['eng-GB', 'fre-FR'], $result);
    }

    public function testGetMainLanguagesWithSiteAccess()
    {
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('languages'), null, 'foo')
            ->willReturn(['eng-GB', 'fre-FR']);

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            [],
            'default'
        );

        $result = $siteAccessHelper->getLanguages(1542, 'foo');

        $this->assertEquals(
            ['eng-GB'], $result
        );
    }

    public function testGetLanguagesByCustomerId()
    {
        $this->configResolver
            ->expects(self::once())
            ->method('getParameter')
            ->with($this->equalTo('languages'), null, 'foo')
            ->willReturn(['eng-GB', 'fre-FR']);

        $siteAccessConfig = [
            'default' => [
                'authentication' => [
                    'customer_id' => 1,
                ],
            ],
            'foo' => [
                'authentication' => [
                    'customer_id' => 123,
                ],
            ],
        ];

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            $siteAccessConfig,
            'default'
        );

        // should return only one language: main language by matched siteAccess
        $this->assertEquals(
            ['eng-GB'],
            $siteAccessHelper->getLanguages(123, null)
        );
    }

    public function testGetSiteAccessesByCustomerIdWithoutCustomerId()
    {
        $this->siteAccessService
            ->expects(self::atLeastOnce())
            ->method('getCurrent')
            ->willReturn(new SiteAccess('foo', 'test'));

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            [],
            'default'
        );

        $result = $siteAccessHelper->getSiteAccessesByCustomerId(null);

        $this->assertEquals(['foo'], $result);
    }

    public function testGetSiteAccessesByCustomerId()
    {
        $siteAccessConfig = [
            'default' => [
                'authentication' => [
                    'customer_id' => 1,
                ],
            ],
            'foo' => [
                'authentication' => [
                    'customer_id' => 2,
                ],
            ],
            'bar' => [
                'authentication' => [
                    'customer_id' => 3,
                ],
            ],
        ];

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            $siteAccessConfig,
            'default'
        );

        $result = $siteAccessHelper->getSiteAccessesByCustomerId(1);

        $this->assertEquals(['default'], $result);
    }

    public function testGetSiteAccessesByCustomerIdWithChangedDefaultSiteAccess()
    {
        $siteAccessConfig = [
            'default' => [
                'authentication' => [
                    'customer_id' => 1,
                ],
            ],
            'foo' => [
                'authentication' => [
                    'customer_id' => 2,
                ],
            ],
            'bar' => [
                'authentication' => [
                    'customer_id' => 3,
                ],
            ],
        ];

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            $siteAccessConfig,
            'foo'
        );

        $result = $siteAccessHelper->getSiteAccessesByCustomerId(1);

        $this->assertEquals(['default'], $result);
    }

    public function testGetSiteAccessesByCustomerIdWithChangedDefaultSiteAccessDifferentCustomerId()
    {
        $siteAccessConfig = [
            'default' => [
                'authentication' => [
                    'customer_id' => 1,
                ],
            ],
            'foo' => [
                'authentication' => [
                    'customer_id' => 2,
                ],
            ],
            'bar' => [
                'authentication' => [
                    'customer_id' => 3,
                ],
            ],
        ];

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            $siteAccessConfig,
            'foo'
        );

        $result = $siteAccessHelper->getSiteAccessesByCustomerId(2);

        $this->assertEquals(['foo'], $result);
    }

    public function testGetSiteAccessesByCustomerIdWithMultipleConfig()
    {
        $siteAccessConfig = [
            'default' => [
                'authentication' => [
                    'customer_id' => 3,
                ],
            ],
            'foo' => [
                'authentication' => [
                    'customer_id' => 3,
                ],
            ],
            'bar' => [
                'authentication' => [
                    'customer_id' => 3,
                ],
            ],
        ];

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            $siteAccessConfig,
            'default'
        );

        $result = $siteAccessHelper->getSiteAccessesByCustomerId(3);

        $this->assertEquals(['default', 'foo', 'bar'], $result);
    }

    public function testGetSiteAccessesByCustomerIdWithChangedDefaultSiteAccessAndMultipleConfig()
    {
        $siteAccessConfig = [
            'default' => [
                'authentication' => [
                    'customer_id' => 3,
                ],
            ],
            'foo' => [
                'authentication' => [
                    'customer_id' => 3,
                ],
            ],
            'bar' => [
                'authentication' => [
                    'customer_id' => 3,
                ],
            ],
        ];

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            $siteAccessConfig,
            'foo'
        );

        $result = $siteAccessHelper->getSiteAccessesByCustomerId(3);

        $this->assertEquals(['default', 'foo', 'bar'], $result);
    }

    public function testGetSiteAccessesByCustomerIdWithWrongCustomerId()
    {
        $siteAccessConfig = [
            'default' => [
                'authentication' => [
                    'customer_id' => 3,
                ],
            ],
            'foo' => [
                'authentication' => [
                    'customer_id' => 3,
                ],
            ],
        ];

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            $siteAccessConfig
        );

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Could not find \'configuration for eZ Recommendation\' with identifier \'customerId: 1007\'');

        $siteAccessHelper->getSiteAccessesByCustomerId(1007);
    }

    public function testGetSiteAccesses()
    {
        $this->siteAccessService
            ->expects(self::atLeastOnce())
            ->method('getCurrent')
            ->willReturn(new SiteAccess('default', 'test'));

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            [],
            'default'
        );

        $result = $siteAccessHelper->getSiteAccesses(null, null);

        $this->assertEquals(['default'], $result);
    }

    public function testGetSiteAccessesWithCustomerId()
    {
        $siteAccessConfig = [
            'foo' => [
                'authentication' => [
                    'customer_id' => 123,
                ],
            ],
        ];

        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            $siteAccessConfig,
            'foo'
        );

        $result = $siteAccessHelper->getSiteAccesses(123, null);

        $this->assertEquals(['foo'], $result);
    }

    public function testGetSiteAccessesWithSiteAccess()
    {
        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            [],
            'foo'
        );

        $result = $siteAccessHelper->getSiteAccesses(null, 'foo');

        $this->assertEquals(['foo'], $result);
    }

    public function testGetSiteAccessesWithWrongCustomerId()
    {
        $siteAccessHelper = new SiteAccessHelper(
            $this->configResolver,
            $this->siteAccessService,
            [],
            'foo'
        );

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Could not find \'configuration for eZ Recommendation\' with identifier \'customerId: 123\'');
        $siteAccessHelper->getSiteAccesses(123, null);
    }
}
