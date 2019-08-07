<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Api;

class AllowedApi
{
    /**
     * @return array
     */
    public function getAllowedApi(): array
    {
        return [
            Recommendation::API_NAME => Recommendation::class,
            EventTracking::API_NAME => EventTracking::class,
            Notifier::API_NAME => Notifier::class,
            User::API_NAME => User::class,
        ];
    }
}
