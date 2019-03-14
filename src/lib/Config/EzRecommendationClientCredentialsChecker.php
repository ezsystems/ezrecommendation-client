<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Config;

use EzSystems\EzRecommendationClient\Value\Config\Credentials;
use EzSystems\EzRecommendationClient\Value\Config\EzRecommendationClientCredentials;
use Psr\Log\LoggerInterface;

class EzRecommendationClientCredentialsChecker extends CredentialsChecker
{
    /** @var int */
    private $customerId;

    /** @var string */
    private $licenseKey;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param int|null $customerId
     * @param string|null $licenseKey
     */
    public function __construct(
        LoggerInterface $logger,
        ?int $customerId,
        ?string $licenseKey
    ) {
        $this->customerId = $customerId;
        $this->licenseKey = $licenseKey;

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

        return new EzRecommendationClientCredentials($this->getRequiredCredentials());
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredCredentials(): array
    {
        return [
            'customerId' => $this->customerId,
            'licenseKey' => $this->licenseKey,
        ];
    }
}
