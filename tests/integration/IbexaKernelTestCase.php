<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Personalization;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class IbexaKernelTestCase extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IbexaTestKernel::class;
    }
}
