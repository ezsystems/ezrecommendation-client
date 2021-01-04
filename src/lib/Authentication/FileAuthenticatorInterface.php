<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Authentication;

interface FileAuthenticatorInterface extends AuthenticatorInterface
{
    /**
     * @param string $filePath
     *
     * @return bool
     */
    public function authenticateByFile(string $filePath): bool;
}
