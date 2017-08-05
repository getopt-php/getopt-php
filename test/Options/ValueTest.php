<?php

namespace GetOpt\Test\Options;

use GetOpt\GetOpt;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{
    /** @dataProvider dataOptionsWithoutDefault */
    public function testValueWithoutDefault(Option $option, $expected)
    {
        $result = $option->value();

        self::assertSame($expected, $result);
    }

    /** @dataProvider dataOptionsWithoutDefault */
    public function testValueWithoutDefaultButSetValue(Option $option, $dummy, $value, $expected)
    {
        $option->setValue($value);

        $result = $option->value();

        self::assertSame($expected, $result);
    }

    public function dataOptionsWithoutDefault()
    {
        return [
            [ Option::create('a', null, GetOpt::NO_ARGUMENT), null, null, 1],
            [ Option::create('a', null, GetOpt::OPTIONAL_ARGUMENT), null, null, 1],
            [ Option::create('a', null, GetOpt::OPTIONAL_ARGUMENT), null, 'val', 'val'],
            [ Option::create('a', null, GetOpt::REQUIRED_ARGUMENT), null, 'val', 'val'],
            [ Option::create('a', null, GetOpt::MULTIPLE_ARGUMENT), [], 'val', ['val']],
        ];
    }

    public function testToStringWithoutArgument()
    {
        $option = new Option('a', null);
        $option->setValue(null);
        $option->setValue(null);

        $this->assertSame('2', (string)$option);
    }

    public function testToStringWithArgument()
    {
        $option = new Option('a', null, GetOpt::REQUIRED_ARGUMENT);
        $option->setValue('valueA');

        $this->assertSame('valueA', (string)$option);
    }

    public function testToStringWithMultipleArguments()
    {
        $option = new Option('a', null, GetOpt::MULTIPLE_ARGUMENT);
        $option->setValue('valueA');
        $option->setValue('valueB');

        $this->assertSame('valueA,valueB', (string)$option);
    }
}
