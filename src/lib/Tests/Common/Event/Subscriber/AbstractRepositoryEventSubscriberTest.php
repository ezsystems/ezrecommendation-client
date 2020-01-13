<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Common\Event\Subscriber;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use EzSystems\EzRecommendationClient\Helper\ContentHelper;
use EzSystems\EzRecommendationClient\Helper\LocationHelper;

abstract class AbstractRepositoryEventSubscriberTest extends AbstractCoreEventSubscriberTest
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\API\Repository\ContentService */
    protected $contentServiceMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\API\Repository\LocationService */
    protected $locationServiceMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\EzSystems\EzRecommendationClient\Helper\LocationHelper */
    protected $locationHelperMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\EzSystems\EzRecommendationClient\Helper\ContentHelper */
    protected $contentHelperMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->contentServiceMock = $this->createMock(ContentService::class);
        $this->locationServiceMock = $this->createMock(LocationService::class);
        $this->locationHelperMock = $this->createMock(LocationHelper::class);
        $this->contentHelperMock = $this->createMock(ContentHelper::class);
    }
}
