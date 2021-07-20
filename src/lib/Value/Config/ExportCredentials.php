<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value\Config;

final class ExportCredentials extends Credentials
{
    public const METHOD_KEY = 'method';
    public const LOGIN_KEY = 'login';
    public const PASSWORD_KEY = 'password';

    private ?string $method;

    private ?string $login;

    private ?string $password;

    public function __construct(
        ?string $method = null,
        ?string $login = null,
        ?string $password = null
    ) {
        $this->method = $method;
        $this->login = $login;
        $this->password = $password;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @phpstan-param array{
     *  'method': ?string,
     *  'login': ?string,
     *  'password': ?string,
     * } $credentials
     */
    public static function fromArray(array $credentials): self
    {
        return new self(
            $credentials[self::METHOD_KEY] ?? null,
            $credentials[self::LOGIN_KEY] ?? null,
            $credentials[self::PASSWORD_KEY] ?? null,
        );
    }
}
