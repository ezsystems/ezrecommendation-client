<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Helper;

use eZ\Publish\Core\MVC\Symfony\SiteAccess as CurrentSiteAccess;
use EzSystems\EzRecommendationClient\Helper\SiteAccessHelper;
use PHPUnit\Framework\TestCase;

class SiteAccessHelperTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function setUp(): void
    {
        parent::setUp();

        $this->configResolver = $this->getMockBuilder('eZ\Publish\Core\MVC\ConfigResolverInterface')->getMock();
    }

    public function testGetRootLocationBySiteAccessNameWithoutParameterSpecified()
    {
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('content.tree_root.location_id'))
            ->willReturn(123)
        ;

        $siteAccess = new CurrentSiteAccess('foo', 'test');

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            [],
            'default'
        );

        $result = $siteAccessMock->getRootLocationBySiteAccessName(null);

        $this->assertEquals(123, $result);
    }

    public function testGetRootLocationBySiteAccessName()
    {
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('content.tree_root.location_id'), null, 'foo')
            ->willReturn(123)
        ;

        $siteAccess = new CurrentSiteAccess('foo', 'test');

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            [],
            'default'
        );

        $result = $siteAccessMock->getRootLocationBySiteAccessName('foo');

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
            ->willReturn(1)
        ;

        $this->configResolver
            ->expects($this->at(1))
            ->method('getParameter')
            ->with($this->equalTo('content.tree_root.location_id'), null, 'cde')
            ->willReturn(2)
        ;

        $siteAccess = new CurrentSiteAccess('foo', 'test');

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            [],
            'default'
        );

        $result = $siteAccessMock->getRootLocationsBySiteAccesses($siteAccesses);

        $this->assertEquals([1, 2], $result);
    }

    public function testGetLanguagesNoParameters()
    {
        $this->configResolver
            ->expects($this->once())
            ->method('getLanguages')
            ->with($this->equalTo('languages'))
            ->willReturn(['eng-GB', 'fre-FR'])
        ;

        $siteAccess = new CurrentSiteAccess('foo', 'test');

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            [],
            'default'
        );

        $result = $siteAccessMock->getLanguages(1111, null);

        $this->assertEquals(['eng-GB', 'fre-FR'], $result);
    }

    public function testGetLanguagesWithSiteAccess()
    {
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('languages'), null, 'foo')
            ->willReturn(['eng-GB', 'fre-FR'])
        ;

        $siteAccess = new CurrentSiteAccess('foo', 'test');

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            [],
            'default'
        );

        $result = $siteAccessMock->getLanguages(1542, 'foo');

        $this->assertEquals(['eng-GB', 'fre-FR'], $result);
    }

    public function testGetLanguagesByCustomerId()
    {
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('languages'), null, 'foo')
            ->willReturn(['eng-GB', 'fre-FR'])
        ;

        $siteAccess = new CurrentSiteAccess('foo', 'test');

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

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            $siteAccessConfig,
            'default'
        );

        $result = $siteAccessMock->getLanguages(123, null);

        // should return only one language: main language by matched siteAccess
        $this->assertEquals(['eng-GB'], $result);
    }

    public function testgetSiteAccessesByCustomerIdWithoutCustomerId()
    {
        $siteAccess = new CurrentSiteAccess('foo', 'test');

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            [],
            'default'
        );

        $result = $siteAccessMock->getSiteAccessesByCustomerId(null);

        $this->assertEquals(['foo'], $result);
    }

    public function testgetSiteAccessesByCustomerId()
    {
        $siteAccess = new CurrentSiteAccess('foo', 'test');

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

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            $siteAccessConfig,
            'default'
        );

        $result = $siteAccessMock->getSiteAccessesByCustomerId(1);

        $this->assertEquals(['default'], $result);
    }

    public function testgetSiteAccessesByCustomerIdWithChangedDefaultSiteAccess()
    {
        $siteAccess = new CurrentSiteAccess('foo', 'test');

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

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            $siteAccessConfig,
            'foo'
        );

        $result = $siteAccessMock->getSiteAccessesByCustomerId(1);

        $this->assertEquals(['default'], $result);
    }

    public function testgetSiteAccessesByCustomerIdWithChangedDefaultSiteAccessDifferentCustomerId()
    {
        $siteAccess = new CurrentSiteAccess('foo', 'test');

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

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            $siteAccessConfig,
            'foo'
        );

        $result = $siteAccessMock->getSiteAccessesByCustomerId(2);

        $this->assertEquals(['foo'], $result);
    }

    public function testgetSiteAccessesByCustomerIdWithMultipleConfig()
    {
        $siteAccess = new CurrentSiteAccess('foo', 'test');

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

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            $siteAccessConfig,
            'default'
        );

        $result = $siteAccessMock->getSiteAccessesByCustomerId(3);

        $this->assertEquals(['default', 'foo', 'bar'], $result);
    }

    public function testgetSiteAccessesByCustomerIdWithChangedDefaultSiteAccessAndMultipleConfig()
    {
        $siteAccess = new CurrentSiteAccess('foo', 'test');

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

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            $siteAccessConfig,
            'foo'
        );

        $result = $siteAccessMock->getSiteAccessesByCustomerId(3);

        $this->assertEquals(['default', 'foo', 'bar'], $result);
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     * @expectedExceptionMessage Could not find 'configuration for eZ Recommendation' with identifier 'customerId: 1007'
     */
    public function testGetSiteAccessesByCustomerIdWithWrongCustomerId()
    {
        $siteAccess = new CurrentSiteAccess('foo', 'test');

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

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            $siteAccessConfig
        );

        $result = $siteAccessMock->getSiteAccessesByCustomerId(1007);
    }

    public function testGetSiteAccesses()
    {
        $siteAccess = new CurrentSiteAccess('default', 'test');

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            [],
            'foo'
        );

        $result = $siteAccessMock->getSiteAccesses(null, null);

        $this->assertEquals(['default'], $result);
    }

    public function testGetSiteAccessesWithCustomerId()
    {
        $siteAccess = new CurrentSiteAccess('default', 'test');

        $siteAccessConfig = [
            'foo' => [
                'authentication' => [
                    'customer_id' => 123,
                ],
            ],
        ];

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            $siteAccessConfig,
            'foo'
        );

        $result = $siteAccessMock->getSiteAccesses(123, null);

        $this->assertEquals(['foo'], $result);
    }

    public function testGetSiteAccessesWithSiteAccess()
    {
        $siteAccess = new CurrentSiteAccess('default', 'test');

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            [],
            'foo'
        );

        $result = $siteAccessMock->getSiteAccesses(null, 'foo');

        $this->assertEquals(['foo'], $result);
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     * @expectedExceptionMessage Could not find 'configuration for eZ Recommendation' with identifier 'customerId: 123'
     */
    public function testGetSiteAccessesWithWrongCustomerId()
    {
        $siteAccess = new CurrentSiteAccess('default', 'test');

        $siteAccessMock = new SiteAccessHelper(
            $this->configResolver,
            $siteAccess,
            [],
            'foo'
        );

        $siteAccessMock->getSiteAccesses(123, null);
    }
}
