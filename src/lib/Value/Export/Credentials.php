<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Value\Export;

use JsonSerializable;

/**
 * Returns credentials used by recommendation engine to fetch data after full import.
 */
final class Credentials implements JsonSerializable
{
    private ?string $login;

    private ?string $password;

    public function __construct(
        ?string $login = null,
        ?string $password = null
    ) {
        $this->login = $login;
        $this->password = $password;
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
     * @return array{
     *  'login': ?string,
     *  'password': ?string,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'login' => $this->getLogin(),
            'password' => $this->getPassword(),
        ];
    }
}
