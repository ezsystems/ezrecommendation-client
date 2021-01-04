<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Templating\Twig;

use EzSystems\EzRecommendationClientBundle\Templating\Twig\Functions\Recommendation;
use EzSystems\EzRecommendationClientBundle\Templating\Twig\Functions\UserTracking;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class RecommendationExtension extends AbstractExtension
{
    /**
     * Returns the name of the extension.
     */
    public function getName(): string
    {
        return 'ez_recommendation_extension';
    }

    /**
     * Returns a list of functions to add to the existing list.
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('ez_recommendation_enabled', [Recommendation::class, 'isRecommendationsEnabled']),
            new TwigFunction('ez_recommendation_track_user', [UserTracking::class, 'trackUser'], [
                'is_safe' => ['html'],
            ]),
        ];
    }
}
