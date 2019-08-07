<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Request;

use EzSystems\EzRecommendationClient\SPI\Request;
use EzSystems\EzRecommendationClient\SPI\UserAPIRequest;

final class UserMetadataRequest extends UserAPIRequest
{
    /** @var bool */
    public $allSources = false;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $parameters = [])
    {
        parent::__construct($this, $parameters);
    }

    /**
     * @return array
     */
    public function getRequestAttributes(): array
    {
        return [
            'allSources' => $this->allSources,
        ];
    }
}
