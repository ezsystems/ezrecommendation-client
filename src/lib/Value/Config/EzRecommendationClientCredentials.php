<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value\Config;

final class EzRecommendationClientCredentials extends Credentials
{
    /** @var int */
    private $customerId;

    /** @var string */
    private $licenseKey;

    /**
     * @param array $credentials
     */
    public function __construct(array $credentials)
    {
        $this->customerId = $credentials['customerId'] ?? null;
        $this->licenseKey = $credentials['licenseKey'] ?? null;
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getLicenseKey(): string
    {
        return $this->licenseKey;
    }
}
