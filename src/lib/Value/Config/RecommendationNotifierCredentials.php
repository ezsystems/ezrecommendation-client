<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value\Config;

class RecommendationNotifierCredentials extends Credentials
{
    /** @var string */
    private $serverUri;

    /** @var string */
    private $apiEndpoint;

    /**
     * @param array $credentials
     */
    public function __construct(array $credentials)
    {
        $this->serverUri = $credentials['serverUri'] ?? null;
        $this->apiEndpoint = $credentials['apiEndpoint'] ?? null;
    }

    /**
     * @return string
     */
    public function getServerUri(): string
    {
        return $this->serverUri;
    }

    /**
     * @return string
     */
    public function getApiEndpoint(): string
    {
        return $this->apiEndpoint;
    }
}
