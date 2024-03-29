<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Client;

use EzSystems\EzRecommendationClient\API\AbstractAPI;
use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use EzSystems\EzRecommendationClient\Exception\BadResponseException;
use EzSystems\EzRecommendationClient\Exception\CredentialsNotFoundException;
use EzSystems\EzRecommendationClient\Factory\EzRecommendationClientAPIFactory;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException as GuzzleBadResponseException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

final class EzRecommendationClient implements EzRecommendationClientInterface
{
    private const DEBUG_MESSAGE = 'ClientDebug: ';
    private const ERROR_MESSAGE = 'ClientError: ';
    private const MESSAGE_SEPARATOR = ' | ';

    /** @var int */
    private $customerId;

    /** @var string */
    private $licenseKey;

    /** @var \GuzzleHttp\ClientInterface */
    private $client;

    /** @var \EzSystems\EzRecommendationClient\Config\EzRecommendationClientCredentialsResolver */
    private $credentialsResolver;

    /** @var \EzSystems\EzRecommendationClient\Factory\EzRecommendationClientAPIFactory */
    private $eZRecommendationClientApiFactory;

    /** @var int|string */
    private $userIdentifier;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(
        ClientInterface $client,
        CredentialsResolverInterface $credentialsResolver,
        EzRecommendationClientAPIFactory $apiFactory,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->credentialsResolver = $credentialsResolver;
        $this->eZRecommendationClientApiFactory = $apiFactory;
        $this->logger = $logger;

        if ($this->credentialsResolver->hasCredentials()) {
            $this->setClientCredentials();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId(int $customerId): EzRecommendationClientInterface
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    /**
     * {@inheritdoc}
     */
    public function setLicenseKey(string $licenseKey): EzRecommendationClientInterface
    {
        $this->licenseKey = $licenseKey;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLicenseKey(): ?string
    {
        return $this->licenseKey;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserIdentifier(string $userIdentifier): EzRecommendationClientInterface
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserIdentifier(): ?string
    {
        return $this->userIdentifier;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendRequest(string $method, UriInterface $uri, array $options = []): ResponseInterface
    {
        try {
            if (!$this->hasCredentials()) {
                $this->logger->warning(self::ERROR_MESSAGE . 'Recommendation credentials are not set', []);

                throw new CredentialsNotFoundException();
            }

            $container = [];
            $history = Middleware::history($container);
            $stack = HandlerStack::create();
            $stack->push($history);

            $response = $this->getHttpClient()->request($method, $uri, array_merge($options, [
                'handler' => $stack,
                'auth' => [
                    $this->getCustomerId(),
                    $this->getLicenseKey(),
                ],
            ]));

            foreach ($container as $transaction) {
                $this->logger->debug(self::DEBUG_MESSAGE . $this->getRequestLogMessage($transaction));
            }

            return $response;
        } catch (GuzzleBadResponseException $exception) {
            $this->logger->error(
                sprintf(
                    self::ERROR_MESSAGE . 'Error while sending data: %s %s %s %s',
                    $exception->getMessage(), $exception->getCode(), $exception->getFile(), $exception->getLine()
                ));

            throw new BadResponseException(
                $exception->getMessage(),
                $exception->getRequest(),
                $exception->getResponse(),
                $exception->getPrevious(),
                $exception->getHandlerContext()
            );
        }
    }

    /**
     * Checks if client has set Recommendation credentials.
     */
    public function hasCredentials(): bool
    {
        return !empty($this->getCustomerId()) && !empty($this->getLicenseKey());
    }

    public function getHeadersAsString(array $headers): string
    {
        $headersAsString = '';

        foreach ($headers as $headerKey => $headerValue) {
            if (isset($headerValue[0])) {
                $headersAsString .= $headerKey . ': ' . $headerValue[0];
            }

            if (next($headers)) {
                $headersAsString .= self::MESSAGE_SEPARATOR;
            }
        }

        return $headersAsString;
    }

    /**
     * {@inheritdoc}
     */
    public function getHttpClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    public function __call(string $name, array $arguments): AbstractAPI
    {
        return $this->eZRecommendationClientApiFactory->buildAPI($name, $this);
    }

    /**
     * Sets client credentials from CredentialsResolver.
     */
    private function setClientCredentials(): void
    {
        $credentials = $this->credentialsResolver->getCredentials();

        $this
            ->setCustomerId($credentials->getCustomerId())
            ->setLicenseKey($credentials->getLicenseKey());
    }

    private function getRequestLogMessage(array $transaction): string
    {
        $message = '';

        if (isset($transaction['request']) && $transaction['request'] instanceof RequestInterface) {
            $requestUri = (string)$transaction['request']->getUri();
            $method = 'Method: ' . $transaction['request']->getMethod();
            $requestHeaders = $this->getHeadersAsString($transaction['request']->getheaders());

            $message .= 'RequestUri: ' . $requestUri . self::MESSAGE_SEPARATOR . $method . self::MESSAGE_SEPARATOR . '?' . $requestHeaders;
        }

        if (isset($transaction['response']) && $transaction['response'] instanceof ResponseInterface) {
            $responseHeaders = $this->getHeadersAsString($transaction['response']->getHeaders());

            $message .= self::MESSAGE_SEPARATOR . $responseHeaders;
        }

        return $message;
    }
}
