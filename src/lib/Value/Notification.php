<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value;

class Notification
{
    /** @var string|null */
    public $transaction;

    /** @var array */
    public $events;

    /** @var int */
    public $customerId;

    /** @var string */
    public $licenseKey;

    /** @var string|null */
    public $endPointUri;
}
