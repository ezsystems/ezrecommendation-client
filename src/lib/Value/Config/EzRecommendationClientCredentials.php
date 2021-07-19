<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value\Config;

final class EzRecommendationClientCredentials extends Credentials
{
    public const CUSTOMER_ID_KEY = 'customerId';
    public const LICENSE_KEY_KEY = 'licenseKey';

    private ?int $customerId;

    private ?string $licenseKey;

    public function __construct(
        ?int $customerId = null,
        ?string $licenseKey = null
    ) {
        $this->customerId = $customerId;
        $this->licenseKey = $licenseKey;
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    public function getLicenseKey(): ?string
    {
        return $this->licenseKey;
    }

    /**
     * @phpstan-param array{
     *  'customerId': ?int,
     *  'licenseKey': ?string,
     * } $credentials
     */
    public static function fromArray(array $credentials): self
    {
        return new self(
            $credentials[self::CUSTOMER_ID_KEY] ?? null,
            $credentials[self::LICENSE_KEY_KEY] ?? null,
        );
    }
}
