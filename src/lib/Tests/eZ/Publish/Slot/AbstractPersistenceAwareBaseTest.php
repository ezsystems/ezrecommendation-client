<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\eZ\Publish\Slot;

abstract class AbstractPersistenceAwareBaseTest extends AbstractSlotTest
{
    /** @var \eZ\Publish\SPI\Persistence\Handler|\PHPUnit_Framework_MockObject_MockObject */
    protected $persistenceHandler;

    public function setUp()
    {
        $this->persistenceHandler = $this->getMockBuilder('\eZ\Publish\SPI\Persistence\Handler')->getMock();

        parent::setUp();
    }

    protected function createSlot()
    {
        $class = $this->getSlotClass();

        return new $class($this->signalSlotServiceMock, $this->persistenceHandler);
    }
}
