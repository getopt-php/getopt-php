<?php

namespace Ulrichsg\Getopt;

class OptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct() {
        $option = new Option('a', 'az-AZ09_', Getopt::OPTIONAL_ARGUMENT);
        $this->assertEquals('a', $option->short());
        $this->assertEquals('az-AZ09_', $option->long());
        $this->assertEquals(Getopt::OPTIONAL_ARGUMENT, $option->mode());
    }

    public function testConstructEmptyOption() {
        $this->setExpectedException('InvalidArgumentException');
        new Option(null, null, Getopt::NO_ARGUMENT);
    }

    public function testConstructNoLetter() {
        $this->setExpectedException('InvalidArgumentException');
        new Option('?', null, Getopt::NO_ARGUMENT);
    }

    public function testConstructInvalidCharacter() {
        $this->setExpectedException('InvalidArgumentException');
        new Option(null, 'öption', Getopt::NO_ARGUMENT);
    }
    public function testConstructInvalidArgumentType() {
        $this->setExpectedException('InvalidArgumentException');
        new Option('a', null, 'no_argument');
    }

    public function testConstructLongOptionTooShort() {
        $this->setExpectedException('InvalidArgumentException');
        new Option(null, 'a', Getopt::REQUIRED_ARGUMENT);
    }

    public function testDefaultValue()
    {
        $option = new Option('a', null);
        $this->assertFalse($option->hasDefaultValue());

        $option->setDefaultValue('foo');
        $this->assertTrue($option->hasDefaultValue());
        $this->assertEquals('foo', $option->getDefaultValue());
    }
}
