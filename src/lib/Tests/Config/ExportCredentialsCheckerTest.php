<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Config;

use EzSystems\EzRecommendationClient\Config\ExportCredentialsChecker;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use EzSystems\EzRecommendationClient\Value\ExportMethod;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ExportCredentialsCheckerTest extends TestCase
{
    public function testCreateExportCredentialsCheckerInstance()
    {
        $this->assertInstanceOf(ExportCredentialsChecker::class, new ExportCredentialsChecker(
            new NullLogger(),
            ExportMethod::BASIC,
            'login',
            'password'
        ));
    }

    /**
     * Test for getCredentials() method.
     */
    public function testGetCredentialsForAuthenticationMethodUser()
    {
        $credentialsChecker = new ExportCredentialsChecker(
            new NullLogger(),
            ExportMethod::USER,
            'login',
            'password'
        );

        $this->assertInstanceOf(ExportCredentials::class, $credentialsChecker->getCredentials());
    }

    /**
     * Test for getCredentials() method.
     */
    public function testReturnNullWhenMethodIsUserAndHasCredentialsIsFalse()
    {
        $credentialsChecker = new ExportCredentialsChecker(
            new NullLogger(),
            ExportMethod::USER,
            '',
            ''
        );

        $this->assertNull($credentialsChecker->getCredentials());
    }
}
