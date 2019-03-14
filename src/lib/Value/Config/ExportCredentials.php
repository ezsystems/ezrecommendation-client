<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value\Config;

final class ExportCredentials extends Credentials
{
    /** @var string */
    private $method;

    /** @var string */
    private $login;

    /** @var string */
    private $password;

    /**
     * @param array $credentials
     */
    public function __construct(array $credentials)
    {
        $this->method = $credentials['method'] ?? '';
        $this->login = $credentials['login'] ?? '';
        $this->password = $credentials['password'] ?? '';
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
