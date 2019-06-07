<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Helper;

use EzSystems\EzRecommendationClient\Helper\ParamsConverterHelper;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    /**
     * @dataProvider stringLists
     */
    public function testGetIdListFromString($input, $expected)
    {
        $result = ParamsConverterHelper::getIdListFromString($input);

        $this->assertEquals($expected, $result);
        $this->assertInternalType('array', $result);
    }

    public function stringLists()
    {
        return [
            ['123', [123]],
            ['123,456', [123, 456]],
            ['12,34,56', [12, 34, 56]],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage String should be a list of Integers
     */
    public function testGetIdListFromStringWithoutSeparator()
    {
        ParamsConverterHelper::getIdListFromString('1abcd');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage String should be a list of Integers
     */
    public function testGetIdListFromStringWitInvalidArgument()
    {
        ParamsConverterHelper::getIdListFromString('123,abc,456');
    }

    public function getArrayFromStringDataProvider()
    {
        return [
            ['123', ['123']],
            ['123,456', ['123', '456']],
            ['12,34,56', ['12', '34', '56']],
            ['ab', ['ab']],
            ['ab,bc', ['ab', 'bc']],
        ];
    }

    /**
     * @dataProvider getArrayFromStringDataProvider
     */
    public function testGetArrayFromString($input, $expected)
    {
        $result = ParamsConverterHelper::getArrayFromString($input);

        $this->assertEquals($expected, $result);
        $this->assertInternalType('array', $result);
    }
}
