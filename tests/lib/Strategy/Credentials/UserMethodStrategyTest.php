<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\PersonalizationClient\Strategy\Credentials;

use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use Ibexa\PersonalizationClient\Strategy\Credentials\ExportCredentialsStrategyInterface;
use Ibexa\PersonalizationClient\Strategy\Credentials\UserMethodStrategy;
use Ibexa\PersonalizationClient\Value\Export\Credentials;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\PersonalizationClient\Strategy\Credentials\UserMethodStrategy
 */
final class UserMethodStrategyTest extends TestCase
{
    private ExportCredentialsStrategyInterface $credentialsStrategy;

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private CredentialsResolverInterface $credentialsResolver;

    protected function setUp(): void
    {
        $this->credentialsResolver = $this->createMock(CredentialsResolverInterface::class);
        $this->credentialsStrategy = new UserMethodStrategy($this->credentialsResolver);
    }

    public function testGetCredentials(): void
    {
        $this->credentialsResolver
            ->expects(self::once())
            ->method('getCredentials')
            ->with('site')
            ->willReturn($this->getConfiguredExportCredentials());

        self::assertEquals(
            new Credentials('user_login', 'user_password'),
            $this->credentialsStrategy->getCredentials('site')
        );
    }

    private function getConfiguredExportCredentials(): ExportCredentials
    {
        return new ExportCredentials('user', 'user_login', 'user_password');
    }
}
