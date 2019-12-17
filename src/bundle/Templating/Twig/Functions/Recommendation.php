<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Templating\Twig\Functions;

use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class Recommendation implements RuntimeExtensionInterface
{
    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface */
    private $credentialsResolver;

    public function __construct(
        CredentialsResolverInterface $credentialsResolver
    ) {
        $this->credentialsResolver = $credentialsResolver;
    }

    public function isRecommendationsEnabled(): bool
    {
        return $this->credentialsResolver->hasCredentials();
    }
}
