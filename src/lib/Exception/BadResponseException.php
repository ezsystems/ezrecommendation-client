<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exception;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BadResponseException extends TransferException
{
    /** @var \Psr\Http\Message\RequestInterface */
    private $request;

    /** @var \Psr\Http\Message\ResponseInterface|null */
    private $response;

    /** @var array */
    private $handlerContext;

    public function __construct(
        string $message,
        RequestInterface $request,
        ?ResponseInterface $response = null,
        \Exception $previous = null,
        array $handlerContext = []
    ) {
        $code = $response && !($response instanceof PromiseInterface)
            ? $response->getStatusCode()
            : 0;
        parent::__construct($message, $code, $previous);

        $this->request = $request;
        $this->response = $response;
        $this->handlerContext = $handlerContext;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function getHandlerContext(): array
    {
        return $this->handlerContext;
    }
}
