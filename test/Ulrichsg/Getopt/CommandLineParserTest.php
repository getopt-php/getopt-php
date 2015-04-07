<?php

namespace Ulrichsg\Getopt;

class CommandLineParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParseNoOptions()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null)
        ));
        $result = $parser->parse('something');
        $this->assertCount(0, $result->getOptions());
        $operands = $result->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('something', $operands[0]);
    }

    public function testParseUnknownOption()
    {
        $this->setExpectedException('UnexpectedValueException');
        $parser = new CommandLineParser(array(
            new Option('a', null)
        ));
        $parser->parse('-b');
    }

    public function testParseRequiredArgumentMissing()
    {
        $this->setExpectedException('UnexpectedValueException');
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $parser->parse('-a');
    }

    public function testParseMultipleOptionsWithOneHyphen()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
            new Option('b', null)
        ));
        $result = $parser->parse('-ab');

        $this->assertEquals(1, $result->getOption('a'));
        $this->assertEquals(1, $result->getOption('b'));
    }

    public function testParseCumulativeOption()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
            new Option('b', null)
        ));
        $result = $parser->parse('-a -b -a -a');

        $this->assertEquals(3, $result->getOption('a'));
        $this->assertEquals(1, $result->getOption('b'));
    }

    public function testParseCumulativeOptionShort()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
            new Option('b', null)
        ));
        $result = $parser->parse('-abaa');

        $this->assertEquals(3, $result->getOption('a'));
        $this->assertEquals(1, $result->getOption('b'));
    }

    public function testParseShortOptionWithArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $result = $parser->parse('-a value');

        $this->assertEquals('value', $result->getOption('a'));
    }

    public function testParseShortOptionWithQuotedArgument()
    {
        $this->markTestSkipped();
        $parser = new CommandLineParser(array(
                new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $result = $parser->parse('-a "hello world"');

        $this->assertEquals('hello world', $result->getOption('a'));
    }

    public function testParseZeroArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $result = $parser->parse('-a 0');

        $this->assertEquals('0', $result->getOption('a'));
    }

    public function testParseNumericOption()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT),
            new Option('2', null)
        ));
        $result = $parser->parse('-a 2 -2');

        $this->assertEquals('2', $result->getOption('a'));
        $this->assertEquals(1, $result->getOption('2'));
    }

    public function testParseCollapsedShortOptionsRequiredArgumentMissing()
    {
        $this->setExpectedException('UnexpectedValueException');
        $parser = new CommandLineParser(array(
            new Option('a', null),
            new Option('b', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $parser->parse('-ab');
    }

    public function testParseCollapsedShortOptionsWithArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
            new Option('b', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $result = $parser->parse('-ab value');

        $this->assertEquals(1, $result->getOption('a'));
        $this->assertEquals('value', $result->getOption('b'));
    }

    public function testParseNoArgumentOptionAndOperand()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
        ));
        $result = $parser->parse('-a b');

        $this->assertEquals(1, $result->getOption('a'));
        $operands = $result->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('b', $operands[0]);
    }

    public function testParseOperandsOnly()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT),
            new Option('b', null)
        ));
        $result = $parser->parse('-- -a -b');

        $this->assertCount(0, $result->getOptions());
        $operands = $result->getOperands();
        $this->assertCount(2, $operands);
        $this->assertEquals('-a', $operands[0]);
        $this->assertEquals('-b', $operands[1]);
    }

    public function testParseLongOptionWithoutArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $result = $parser->parse('--option');

        $this->assertEquals(1, $result->getOption('option'));
    }

    public function testParseLongOptionWithoutArgumentAndOperand()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::NO_ARGUMENT)
        ));
        $result = $parser->parse('--option something');

        $this->assertEquals(1, $result->getOption('option'));
        $operands = $result->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('something', $operands[0]);
    }

    public function testParseLongOptionWithArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $result = $parser->parse('--option value');

        $this->assertEquals('value', $result->getOption('option'));
        $this->assertEquals('value', $result->getOption('o'));
    }

    public function testParseLongOptionWithEqualsSignAndArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $result = $parser->parse('--option=value something');

        $this->assertEquals('value', $result->getOption('option'));
        $operands = $result->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('something', $operands[0]);
    }

    public function testParseLongOptionWithValueStartingWithHyphen()
    {
        //$this->markTestSkipped();
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::REQUIRED_ARGUMENT)
        ));
        $result = $parser->parse('--option=-value');

        $this->assertEquals('-value', $result->getOption('option'));
    }

    public function testParseNoValueStartingWithHyphenRequired()
    {
        $this->setExpectedException('UnexpectedValueException');
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT),
            new Option('b', null)
        ));
        $parser->parse('-a -b');
    }

    public function testParseNoValueStartingWithHyphenOptional()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::OPTIONAL_ARGUMENT),
            new Option('b', null)
        ));
        $result = $parser->parse('-a -b');

        $this->assertEquals(1, $result->getOption('a'));
        $this->assertEquals(1, $result->getOption('b'));
    }

    public function testParseOptionWithDefaultValue()
    {
        $optionA = new Option('a', null, Getopt::REQUIRED_ARGUMENT);
        $optionA->setArgument(new Argument(10));
        $optionB = new Option('b', 'beta', Getopt::REQUIRED_ARGUMENT);
        $optionB->setArgument(new Argument(20));
        $parser = new CommandLineParser(array($optionA, $optionB));
        $result = $parser->parse('-a 12');

        $this->assertEquals(12, $result->getOption('a'));
        $this->assertEquals(20, $result->getOption('b'));
        $this->assertEquals(20, $result->getOption('beta'));
    }

    public function testDoubleHyphenNotInOperands()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $result = $parser->parse('-a 0 foo -- bar baz');

        $this->assertEquals('0', $result->getOption('a'));
        $operands = $result->getOperands();
        $this->assertCount(3, $operands);
        $this->assertEquals('foo', $operands[0]);
        $this->assertEquals('bar', $operands[1]);
        $this->assertEquals('baz', $operands[2]);
    }

    public function testSingleHyphenValue()
    {
        $parser = new CommandLineParser(array(
            new Option('a', 'alpha', Getopt::REQUIRED_ARGUMENT)
        ));

        $result = $parser->parse('-a -');

        $this->assertEquals('-', $result->getOption('a'));
        $operands = $result->getOperands();
        $this->assertCount(0, $operands);

        $result = $parser->parse('--alpha -');

        $this->assertEquals('-', $result->getOption('a'));
        $operands = $result->getOperands();
        $this->assertCount(0, $operands);
    }
    
    public function testSingleHyphenOperand()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $result = $parser->parse('-a 0 -');

        $this->assertEquals('0', $result->getOption('a'));
        $operands = $result->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('-', $operands[0]);
    }

    public function testParseWithArgumentValidation()
    {
        $validation = 'is_numeric';
        $optionA = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
        $optionA->setArgument(new Argument(null, $validation));
        $optionB = new Option('b', null, Getopt::REQUIRED_ARGUMENT);
        $optionB->setArgument(new Argument(null, $validation));
        $optionC = new Option('c', null, Getopt::OPTIONAL_ARGUMENT);
        $optionC->setArgument(new Argument(null, $validation));
        $parser = new CommandLineParser(array($optionA, $optionB, $optionC));
        $result = $parser->parse('-a 1 -b 2 -c');

        $this->assertSame('1', $result->getOption('a'));
        $this->assertSame('2', $result->getOption('b'));
        $this->assertSame(1, $result->getOption('c'));
    }

    public function testParseInvalidArgument()
    {
        $this->setExpectedException('UnexpectedValueException');
        $validation = 'is_numeric';
        $option = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
        $option->setArgument(new Argument(null, $validation));
        $parser = new CommandLineParser(array($option));
        $parser->parse('-a nonnumeric');
    }
}
