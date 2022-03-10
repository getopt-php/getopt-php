<?php

namespace GetOpt\Test\Options;

use GetOpt\GetOpt;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{
    /** @dataProvider dataOptionsWithoutDefault
     * @param Option $option
     * @param mixed  $expected
     * @test */
    public function valueWithoutDefault(Option $option, $expected): void
    {
        $result = $option->getValue();

        self::assertSame($expected, $result);
    }

    /** @dataProvider dataOptionsWithoutDefault
     * @param Option $option
     * @param mixed  $dummy
     * @param mixed  $value
     * @param mixed  $expected
     * @test */
    public function valueWithoutDefaultButSetValue(Option $option, $dummy, $value, $expected): void
    {
        $option->setValue($value);

        $result = $option->getValue();

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

    /** @test */
    public function toStringWithoutArgument(): void
    {
        $option = new Option('a', null);
        $option->setValue(null);
        $option->setValue(null);

        self::assertSame('2', (string)$option);
    }

    /** @test */
    public function toStringWithArgument(): void
    {
        $option = new Option('a', null, GetOpt::REQUIRED_ARGUMENT);
        $option->setValue('valueA');

        self::assertSame('valueA', (string)$option);
    }

    /** @test */
    public function toStringWithMultipleArguments(): void
    {
        $option = new Option('a', null, GetOpt::MULTIPLE_ARGUMENT);
        $option->setValue('valueA');
        $option->setValue('valueB');

        self::assertSame('valueA,valueB', (string)$option);
    }

    /** @test */
    public function defaultValueNotUsedForCounting(): void
    {
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        $option->setDefaultValue(42);

        $option->setValue(null);

        self::assertSame(1, $option->getValue());
    }
}
