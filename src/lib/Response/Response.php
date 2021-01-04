<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Response;

use EzSystems\EzRecommendationClient\Generator\ContentListElementGenerator;

abstract class Response implements ResponseInterface
{
    /** @var \EzSystems\EzRecommendationClient\Generator\ContentListElementGenerator */
    public $contentListElementGenerator;

    /**
     * @param \EzSystems\EzRecommendationClient\Generator\ContentListElementGenerator $contentListElementGenerator
     */
    public function __construct(ContentListElementGenerator $contentListElementGenerator)
    {
        $this->contentListElementGenerator = $contentListElementGenerator;
    }
}
