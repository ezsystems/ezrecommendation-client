<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Client;

use EzSystems\EzRecommendationClient\Api\AbstractApi;
use EzSystems\EzRecommendationClient\Api\Notifier;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * @method \EzSystems\EzRecommendationClient\Api\Recommendation recommendation()
 * @method \EzSystems\EzRecommendationClient\Api\EventTracking eventTracking()
 * @method \EzSystems\EzRecommendationClient\Api\Notifier notifier()
 * @method \EzSystems\EzRecommendationClient\Api\User user()
 */
interface EzRecommendationClientInterface
{
    /**
     * @param int $customerId
     *
     * @return \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface
     */
    public function setCustomerId(int $customerId): self;

    /**
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * @param string $licenseKey
     *
     * @return \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface
     */
    public function setLicenseKey(string $licenseKey): self;

    /**
     * @return string|null
     */
    public function getLicenseKey(): ?string;

    /**
     * @param string $userIdentifier
     *
     * @return \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface
     */
    public function setUserIdentifier(string $userIdentifier): self;

    /**
     * @return string|null
     */
    public function getUserIdentifier(): ?string;

    /**
     * @param string $method
     * @param \Psr\Http\Message\UriInterface $uri
     * @param array $option
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function sendRequest(string $method, UriInterface $uri, array $option = []): ?ResponseInterface;

    /**
     * @return \GuzzleHttp\ClientInterface
     */
    public function getHttpClient(): ClientInterface;

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return \EzSystems\EzRecommendationClient\Api\AbstractApi
     */
    public function __call(string $name, array $arguments): AbstractApi;
}
