<?php

namespace Ulrichsg\Getopt;

class OptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $option = new Option('a', 'az-AZ09_', Getopt::OPTIONAL_ARGUMENT);
        $this->assertEquals('a', $option->short());
        $this->assertEquals('az-AZ09_', $option->long());
        $this->assertEquals(Getopt::OPTIONAL_ARGUMENT, $option->mode());
    }
    
    /** @dataProvider dataConstructFails */
    public function testConstructFails($short, $long, $mode)
    {
        $this->setExpectedException('InvalidArgumentException');
        new Option($short, $long, $mode);
    }
    
    public function dataConstructFails()
    {
        return array(
            array(null, null, Getopt::NO_ARGUMENT),      // long and short are both empty
            array('?', null, Getopt::NO_ARGUMENT),       // short name must be letter or digit
            array(null, 'öption', Getopt::NO_ARGUMENT),  // long name may contain only alphanumeric chars, _ and -
            array('a', null, 'no_argument'),             // invalid type
            array(null, 'a', Getopt::NO_ARGUMENT)        // long name must be at least 2 characters long
        );
    }

    /** @dataProvider dataMatches */
    public function testMatches(Option $option, $string, $matches)
    {
        $this->assertEquals($matches, $option->matches($string));
    }

    public function dataMatches()
    {
        return array(
            array(new Option('v', null), 'v', true),
            array(new Option(null, 'verbose'), 'verbose', true),
            array(new Option(null, 'verbose'), 'v', false),
            array(new Option('v', 'verbose'), 'v', true)
        );
    }

    /** @dataProvider dataEquals */
    public function testEquals(Option $first, Option $second, $equals)
    {
        $this->assertEquals($equals, $first->equals($second));
    }

    public function dataEquals()
    {
        return array(
            array(new Option('v', null), new Option('v', null), true),
            array(new Option(null, 'verbose'), new Option(null, 'verbose'), true),
            array(new Option('v', 'verbose'), new Option('v', 'verbose'), true),
            array(new Option('v', 'verbose'), new Option('v', 'version'), false),
            array(new Option('v', 'verbose'), new Option('V', 'verbose'), false)
        );
    }

    /** @dataProvider dataConflictsWith */
    public function testConflictsWith(Option $first, Option $second, $conflict)
    {
        $this->assertEquals($conflict, $first->conflictsWith($second));
    }

    public function dataConflictsWith()
    {
        return array(
            array(new Option('v', 'verbose'), new Option('v', 'version'), true),
            array(new Option('v', 'verbose'), new Option('v', 'verbose'), false),
            array(new Option('v', 'verbose'), new Option('v', null), true),
            array(new Option('v', null), new Option('v', null), false)
        );
    }

    public function testSetArgument()
    {
        $option = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
        $this->assertEquals($option, $option->setArgument(new Argument()));
        $this->assertInstanceof('Ulrichsg\Getopt\Argument', $option->getArgument());
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
}
