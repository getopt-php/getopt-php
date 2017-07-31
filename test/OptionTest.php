<?php

namespace GetOpt;

use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    public function testConstruct()
    {
        $option = new Option('a', 'az-AZ09_', GetOpt::OPTIONAL_ARGUMENT);
        $this->assertEquals('a', $option->short());
        $this->assertEquals('az-AZ09_', $option->long());
        $this->assertEquals(GetOpt::OPTIONAL_ARGUMENT, $option->mode());
    }

    public function testCreate()
    {
        $option = Option::create('a', 'az-AZ09_', GetOpt::OPTIONAL_ARGUMENT);
        $this->assertEquals('a', $option->short());
        $this->assertEquals('az-AZ09_', $option->long());
        $this->assertEquals(GetOpt::OPTIONAL_ARGUMENT, $option->mode());
    }

    /** @dataProvider dataConstructFails
     * @param string $short
     * @param string $long
     * @param int $mode
     */
    public function testConstructFails($short, $long, $mode)
    {
        $this->setExpectedException('InvalidArgumentException');
        new Option($short, $long, $mode);
    }

    public function dataConstructFails()
    {
        return [
            [ null, null, GetOpt::NO_ARGUMENT ],      // long and short are both empty
            [ '&', null, GetOpt::NO_ARGUMENT ],       // short name must be one of [a-zA-Z0-9?!§$%#]
            [ null, 'öption', GetOpt::NO_ARGUMENT ],  // long name may contain only alphanumeric chars, _ and -
            [ 'a', null, 'no_argument' ],             // invalid mode
            [ null, 'a', GetOpt::NO_ARGUMENT ]        // long name must be at least 2 characters long
        ];
    }

    public function testSetArgument()
    {
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        $this->assertEquals($option, $option->setArgument(new Argument()));
        $this->assertInstanceof(Argument::CLASSNAME, $option->getArgument());
    }

    public function testSetArgumentWrongMode()
    {
        $this->setExpectedException('InvalidArgumentException');
        $option = new Option('a', null, GetOpt::NO_ARGUMENT);
        $option->setArgument(new Argument());
    }

    public function testSetDefaultValue()
    {
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        $this->assertEquals($option, $option->setDefaultValue(10));
        $this->assertEquals(10, $option->getArgument()->getDefaultValue());
    }

    public function testSetValidation()
    {
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        $this->assertEquals($option, $option->setValidation('is_numeric'));
        $this->assertTrue($option->getArgument()->hasValidation());
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
