<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Config;

use Psr\Log\LoggerInterface;

abstract class CredentialsChecker implements CredentialsCheckerInterface
{
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    abstract protected function getRequiredCredentials(): array;

    /**
     * {@inheritdoc}
     */
    public function hasCredentials(): bool
    {
        $exportCredentials = $this->getRequiredCredentials();

        $missingCredentials = [];

        foreach ($exportCredentials as $credential) {
            if (empty($credential)) {
                $missingCredentials[] = $credential;
            }
        }

        if ($missingCredentials) {
            $this->logger->warning('Following required credentials are missing: ' . implode(', ', $missingCredentials));

            return false;
        }

        return true;
    }
}
