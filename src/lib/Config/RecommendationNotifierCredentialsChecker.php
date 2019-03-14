<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Config;

use EzSystems\EzRecommendationClient\Value\Config\Credentials;
use EzSystems\EzRecommendationClient\Value\Config\RecommendationNotifierCredentials;
use Psr\Log\LoggerInterface;

class RecommendationNotifierCredentialsChecker extends CredentialsChecker
{
    /** @var string|null */
    private $serverUri;

    /** @var string|null */
    private $apiEndpoint;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param string|null $serverUri
     * @param string|null $apiEndpoint
     */
    public function __construct(
        LoggerInterface $logger,
        ?string $serverUri,
        ?string $apiEndpoint
    ) {
        $this->serverUri = $serverUri;
        $this->apiEndpoint = $apiEndpoint;

        parent::__construct($logger);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(): ?Credentials
    {
        if (!$this->hasCredentials()) {
            return null;
        }

        return new RecommendationNotifierCredentials($this->getRequiredCredentials());
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredCredentials(): array
    {
        return [
            'serverUri' => $this->serverUri,
            'apiEndpoint' => $this->apiEndpoint,
        ];
    }
}
