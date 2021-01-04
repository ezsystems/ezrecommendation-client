<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Response;

use eZ\Publish\Core\REST\Common\Output\Generator;

interface ResponseInterface
{
    /**
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param $data
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Generator
     */
    public function render(Generator $generator, $data): Generator;
}
