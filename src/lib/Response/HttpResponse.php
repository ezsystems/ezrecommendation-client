<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Response;

use eZ\Publish\Core\REST\Common\Output\Generator;

class HttpResponse extends Response
{
    /**
     * {@inheritdoc}
     */
    public function render(Generator $generator, $data): Generator
    {
        $contents = [];

        foreach ($data->contents as $contentTypes) {
            foreach ($contentTypes as $contentType) {
                $contents[] = $contentType;
            }
        }

        return $this->contentListElementGenerator->generateElement($generator, $contents);
    }
}
