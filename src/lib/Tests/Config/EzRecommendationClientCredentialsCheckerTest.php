<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Config;

use EzSystems\EzRecommendationClient\Config\EzRecommendationClientCredentialsChecker;
use EzSystems\EzRecommendationClient\Value\Config\EzRecommendationClientCredentials;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class EzRecommendationClientCredentialsCheckerTest extends TestCase
{
    public function testCreateEzRecommendationClientCredentialsCheckerInstance()
    {
        $this->assertInstanceOf(EzRecommendationClientCredentialsChecker::class, new EzRecommendationClientCredentialsChecker(
            new NullLogger(),
            12345,
            '12345-12345-12345-12345-12345'
        ));
    }

    /**
     * Test for getCredentials() method.
     */
    public function testReturnGetEzRecommendationClientCredentials()
    {
        $credentialsChecker = new EzRecommendationClientCredentialsChecker(
            new NullLogger(),
            12345,
            '12345-12345-12345-12345-12345'
        );

        $this->assertInstanceOf(EzRecommendationClientCredentials::class, $credentialsChecker->getCredentials());
    }

    /**
     * Test for getCredentials() method.
     */
    public function testReturnNullWhenCredentialsAreNotSet()
    {
        $credentialsChecker = new EzRecommendationClientCredentialsChecker(
            new NullLogger(),
            null,
            null
        );

        $this->assertNull($credentialsChecker->getCredentials());
    }
}
