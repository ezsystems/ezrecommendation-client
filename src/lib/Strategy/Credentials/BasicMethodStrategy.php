<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Strategy\Credentials;

use EzSystems\EzRecommendationClient\Value\ExportMethod;
use Ibexa\PersonalizationClient\Generator\UniqueStringGeneratorInterface;
use Ibexa\PersonalizationClient\Value\Export\Credentials;

final class BasicMethodStrategy implements ExportCredentialsStrategyInterface
{
    private const LOGIN_LENGTH = 10;
    private const PASSWORD_LENGTH = 30;

    private UniqueStringGeneratorInterface $uniqueStringGenerator;

    public function __construct(UniqueStringGeneratorInterface $uniqueStringGenerator)
    {
        $this->uniqueStringGenerator = $uniqueStringGenerator;
    }

    public function getCredentials(?string $siteAccess = null): Credentials
    {
        return new Credentials(
            $this->uniqueStringGenerator->generate(self::LOGIN_LENGTH),
            $this->uniqueStringGenerator->generate(self::PASSWORD_LENGTH)
        );
    }

    public static function getIndex(): string
    {
        return ExportMethod::BASIC;
    }
}
