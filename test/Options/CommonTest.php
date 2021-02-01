<?php

namespace GetOpt\Test\Options;

use GetOpt\Argument;
use GetOpt\GetOpt;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class CommonTest extends TestCase
{
    /** @test */
    public function construct()
    {
        $option = new Option('a', 'az-AZ09_', GetOpt::OPTIONAL_ARGUMENT);
        self::assertSame('a', $option->getShort());
        self::assertSame('az-AZ09_', $option->getLong());
        self::assertSame(GetOpt::OPTIONAL_ARGUMENT, $option->getMode());
    }

    /** @test */
    public function create()
    {
        $option = Option::create('a', 'az-AZ09_', GetOpt::OPTIONAL_ARGUMENT);
        self::assertSame('a', $option->getShort());
        self::assertSame('az-AZ09_', $option->getLong());
        self::assertSame(GetOpt::OPTIONAL_ARGUMENT, $option->getMode());
    }

    /** @dataProvider dataConstructFails
     * @param string $short
     * @param string $long
     * @param int    $mode
     * @test */
    public function constructFails($short, $long, $mode)
    {
        self::expectException(\InvalidArgumentException::class);
        new Option($short, $long, $mode);
    }

    public function dataConstructFails()
    {
        return [
            [ null, null, GetOpt::NO_ARGUMENT ],      // long and short are both empty
            [ 'a', 'a', GetOpt::NO_ARGUMENT ],        // long and short are same
            [ '&', null, GetOpt::NO_ARGUMENT ],       // short name must be one of [a-zA-Z0-9?!§$%#]
            [ null, 'öption', GetOpt::NO_ARGUMENT ],  // long name may contain only alphanumeric chars, _ and -
            [ 'a', null, 'no_argument' ],             // invalid mode
        ];
    }

    /** @test */
    public function setArgument()
    {
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        self::assertSame($option, $option->setArgument(new Argument()));
        self::assertInstanceof(Argument::CLASSNAME, $option->getArgument());
    }

    /** @test */
    public function setArgumentWrongMode()
    {
        self::expectException(\InvalidArgumentException::class);
        $option = new Option('a', null, GetOpt::NO_ARGUMENT);
        $option->setArgument(new Argument());
    }

    /** @test */
    public function setDefaultValue()
    {
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        self::assertSame($option, $option->setDefaultValue(10));
        self::assertSame(10, $option->getArgument()->getDefaultValue());
    }

    /** @test */
    public function setValidation()
    {
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        self::assertSame($option, $option->setValidation('is_numeric'));
        self::assertTrue($option->getArgument()->hasValidation());
    }
}
