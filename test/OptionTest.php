<?php

namespace GetOpt;

use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
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
            [ null, null, Getopt::NO_ARGUMENT ],      // long and short are both empty
            [ '&', null, Getopt::NO_ARGUMENT ],       // short name must be one of [a-zA-Z0-9?!§$%#]
            [ null, 'öption', Getopt::NO_ARGUMENT ],  // long name may contain only alphanumeric chars, _ and -
            [ 'a', null, 'no_argument' ],             // invalid mode
            [ null, 'a', Getopt::NO_ARGUMENT ]        // long name must be at least 2 characters long
        ];
    }

    /** @dataProvider dataMatches
     * @param Option $option
     * @param string $string
     * @param bool   $matches
     */
    public function testMatches(Option $option, $string, $matches)
    {
        $this->assertEquals($matches, $option->matches($string));
    }
    public function dataMatches()
    {
        return [
            [ new Option('v', null), 'v', true ],
            [ new Option(null, 'verbose'), 'verbose', true ],
            [ new Option(null, 'verbose'), 'v', false ],
            [ new Option('v', 'verbose'), 'v', true ]
        ];
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

    public function testSetArgumentFromConstructor()
    {
        $argument = new Argument();

        $option = new Option('a', null, Getopt::OPTIONAL_ARGUMENT, $argument);

        self::assertSame($argument, $option->getArgument());
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
