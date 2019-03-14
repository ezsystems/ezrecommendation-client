<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Config;

use EzSystems\EzRecommendationClient\Value\Config\Credentials;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use EzSystems\EzRecommendationClient\Value\ExportMethod;
use Psr\Log\LoggerInterface;

class ExportCredentialsChecker extends CredentialsChecker
{
    /** @var bool */
    private $method;

    /** @var string */
    private $login;

    /** @var string */
    private $password;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $method
     * @param string|null $login
     * @param string|null $password
     */
    public function __construct(
        LoggerInterface $logger,
        string $method,
        ?string $login,
        ?string $password
    ) {
        $this->method = $method;
        $this->login = $login;
        $this->password = $password;

        parent::__construct($logger);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(): ?Credentials
    {
        if ($this->method === ExportMethod::USER && !$this->hasCredentials()) {
            return null;
        }

        return new ExportCredentials($this->getRequiredCredentials());
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredCredentials(): array
    {
        return [
            'method' => $this->method,
            'login' => $this->login,
            'password' => $this->password,
        ];
    }
}
