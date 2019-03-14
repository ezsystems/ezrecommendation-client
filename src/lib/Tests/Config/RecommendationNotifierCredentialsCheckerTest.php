<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Config;

use EzSystems\EzRecommendationClient\Config\RecommendationNotifierCredentialsChecker;
use EzSystems\EzRecommendationClient\Value\Config\RecommendationNotifierCredentials;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class RecommendationNotifierCredentialsCheckerTest extends TestCase
{
    public function testCreateRecommendationNotifierCredentialsCheckerInstance()
    {
        $this->assertInstanceOf(RecommendationNotifierCredentialsChecker::class, new RecommendationNotifierCredentialsChecker(
            new NullLogger(),
            'server_uri',
            'api_endpoint'
        ));
    }

    /**
     * Test for getCredentials() method.
     */
    public function testReturnRecommendationNotifierCredentials()
    {
        $credentialsChecker = new RecommendationNotifierCredentialsChecker(
            new NullLogger(),
            'server_uri',
            'api_endpoint'
        );

        $this->assertInstanceOf(RecommendationNotifierCredentials::class, $credentialsChecker->getCredentials());
    }

    /**
     * Test for getCredentials() method.
     */
    public function testReturnNullWhenCredentialsAreNotSet()
    {
        $credentialsChecker = new RecommendationNotifierCredentialsChecker(
            new NullLogger(),
            null,
            null
        );

        $this->assertNull($credentialsChecker->getCredentials());
    }
}
