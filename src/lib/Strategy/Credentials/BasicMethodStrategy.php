<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Strategy\Credentials;

use EzSystems\EzRecommendationClient\Value\ExportMethod;
use Ibexa\PersonalizationClient\Generator\Password\PasswordGeneratorInterface;
use Ibexa\PersonalizationClient\Value\Export\Credentials;

final class BasicMethodStrategy implements ExportCredentialsStrategyInterface
{
    private const USER_LOGIN = 'ibx';

    private PasswordGeneratorInterface $passwordGenerator;

    public function __construct(PasswordGeneratorInterface $passwordGenerator)
    {
        $this->passwordGenerator = $passwordGenerator;
    }

    public function getCredentials(?string $siteAccess = null): Credentials
    {
        return new Credentials(self::USER_LOGIN, $this->passwordGenerator->generate());
    }

    public static function getIndex(): string
    {
        return ExportMethod::BASIC;
    }
}
