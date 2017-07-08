<?php

namespace GetOpt;

class OptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $option = new Option('a', 'az-AZ09_', Getopt::OPTIONAL_ARGUMENT);
        $this->assertEquals('a', $option->short());
        $this->assertEquals('az-AZ09_', $option->long());
        $this->assertEquals(Getopt::OPTIONAL_ARGUMENT, $option->mode());
    }

    public function testCreate()
    {
        $option = Option::create('a', 'az-AZ09_', Getopt::OPTIONAL_ARGUMENT);
        $this->assertEquals('a', $option->short());
        $this->assertEquals('az-AZ09_', $option->long());
        $this->assertEquals(Getopt::OPTIONAL_ARGUMENT, $option->mode());
    }

    public function testConstructEmptyOption()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Option(null, null, Getopt::NO_ARGUMENT);
    }

    public function testConstructNoLetter()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Option('?', null, Getopt::NO_ARGUMENT);
    }

    public function testConstructInvalidCharacter()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Option(null, 'öption', Getopt::NO_ARGUMENT);
    }

    public function testConstructInvalidArgumentType()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Option('a', null, 'no_argument');
    }

    public function testConstructLongOptionTooShort()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Option(null, 'a', Getopt::REQUIRED_ARGUMENT);
    }

    public function testSetArgument()
    {
        $option = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
        $this->assertEquals($option, $option->setArgument(new Argument()));
        $this->assertInstanceof(Argument::CLASSNAME, $option->getArgument());
    }

    public function testSetArgumentWrongMode()
    {
        $this->setExpectedException('InvalidArgumentException');
        $option = new Option('a', null, Getopt::NO_ARGUMENT);
        $option->setArgument(new Argument());
    }

    public function testSetDefaultValue()
    {
        $option = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
        $this->assertEquals($option, $option->setDefaultValue(10));
        $this->assertEquals(10, $option->getArgument()->getDefaultValue());
    }

    public function testSetValidation()
    {
        $option = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
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
        $option = new Option('a', null, Getopt::REQUIRED_ARGUMENT);
        $option->setValue('valueA');

        $this->assertSame('valueA', (string)$option);
    }

    public function testToStringWithMultipleArguments()
    {
        $option = new Option('a', null, Getopt::MULTIPLE_ARGUMENT);
        $option->setValue('valueA');
        $option->setValue('valueB');

        $this->assertSame('valueA,valueB', (string)$option);
    }
}
