<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Client;

use EzSystems\EzRecommendationClient\API\AbstractAPI;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * @method \EzSystems\EzRecommendationClient\API\Recommendation recommendation()
 * @method \EzSystems\EzRecommendationClient\API\EventTracking eventTracking()
 * @method \EzSystems\EzRecommendationClient\API\Notifier notifier()
 * @method \EzSystems\EzRecommendationClient\API\User user()
 */
interface EzRecommendationClientInterface
{
    /**
     * @return \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface
     */
    public function setCustomerId(?int $customerId = null): self;

    public function getCustomerId(): ?int;

    /**
     * @return \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface
     */
    public function setLicenseKey(?string $licenseKey = null): self;

    public function getLicenseKey(): ?string;

    /**
     * @return \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface
     */
    public function setUserIdentifier(string $userIdentifier): self;

    public function getUserIdentifier(): ?string;

    public function sendRequest(string $method, UriInterface $uri, array $option = []): ResponseInterface;

    public function getHttpClient(): ClientInterface;

    public function __call(string $name, array $arguments): AbstractAPI;
}
