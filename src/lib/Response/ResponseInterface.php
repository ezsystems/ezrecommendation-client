<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Response;

use EzSystems\EzPlatformRest\Output\Generator;

interface ResponseInterface
{
    /**
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param $data
     * 
     * @return \EzSystems\EzPlatformRest\Output\Generator
     */
    public function render(Generator $generator, $data): Generator;
}
