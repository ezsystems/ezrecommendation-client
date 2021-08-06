<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service\Notification;

use EzSystems\EzRecommendationClient\SPI\Notification;
use Psr\Http\Message\ResponseInterface;

interface NotificationServiceInterface
{
    public function send(Notification $notification, string $action): ?ResponseInterface;
}
