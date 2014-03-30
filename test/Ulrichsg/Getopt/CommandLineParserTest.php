<?php

namespace Ulrichsg\Getopt;

class CommandLineParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParseNoOptions()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null)
        ));
        $parser->parse('something');
        $this->assertCount(0, $parser->getOptions());
        $operands = $parser->getOperands();
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
        $parser->parse('-ab');

        $options = $parser->getOptions();
        $this->assertEquals(1, $options['a']);
        $this->assertEquals(1, $options['b']);
    }

    public function testParseCumulativeOption()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
            new Option('b', null)
        ));
        $parser->parse('-a -b -a -a');

        $options = $parser->getOptions();
        $this->assertEquals(3, $options['a']);
        $this->assertEquals(1, $options['b']);
    }

    public function testParseCumulativeOptionShort()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
            new Option('b', null)
        ));
        $parser->parse('-abaa');

        $options = $parser->getOptions();
        $this->assertEquals(3, $options['a']);
        $this->assertEquals(1, $options['b']);
    }

    public function testParseShortOptionWithArgument()
    {
       // $g = new Getopt( array( new Option("a","aa", Getopt::OPTIONAL_ARGUMENT) ) );
        
        $validation = 'is_numeric';
        $optionA = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
        $optionA->setArgument(new Argument(null, $validation));
        $optionB = new Option('b', null, Getopt::REQUIRED_ARGUMENT);
        $optionB->setArgument(new Argument(null, $validation));
        $optionC = new Option('c', null, Getopt::OPTIONAL_ARGUMENT);
        $optionC->setArgument(new Argument(null, $validation));
        $parser = new CommandLineParser(array($optionA, $optionB, $optionC));
        $parser->parse('-a 1 -b 2 -c 5');

        $options = $parser->getOptions();
        $this->assertSame(1, $options['a']);  
    }

    public function testParseZeroArgument()
    {
        $validation = 'is_numeric';
        $optionA = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
        $optionA->setArgument(new Argument(null, $validation));
        $optionB = new Option('b', null, Getopt::REQUIRED_ARGUMENT);
        $optionB->setArgument(new Argument(null, $validation));
        $optionC = new Option('c', null, Getopt::OPTIONAL_ARGUMENT);
        $optionC->setArgument(new Argument(null, $validation));
        $parser = new CommandLineParser(array($optionA, $optionB, $optionC));
        $parser->parse('-a "0" -b "0" -c 0');

        $options = $parser->getOptions();
        $this->assertSame(1, $options['a']);  
    }

    public function testParseNumericOption()
    {
        $validation = 'is_numeric';
        $optionA = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
        $optionA->setArgument(new Argument(null, $validation));
        $parser = new CommandLineParser(array($optionA, ));
        $parser->parse('-a 1 23');

        $options = $parser->getOptions();
        $this->assertSame(1, $options['a']);  
    }

    public function testParseCollapsedShortOptionsRequiredArgumentMissing()
    {
        $this->setExpectedException('UnexpectedValueException');
        $parser = new CommandLineParser(array(
            new Option('a', null),
        ));
        $parser->parse('-ab');
    }

    public function testParseCollapsedShortOptionsWithArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
            new Option('b', null, Getopt::OPTIONAL_ARGUMENT)
        ));
        $parser->parse('-a 12 -b 1 -a 1');

        $options = $parser->getOptions();
        //$this->assertEquals(1, $options['a']);
        $this->assertEquals(1, $options['b']);
    }

    public function testParseNoArgumentOptionAndOperand()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
        ));
        $parser->parse('-a b');

        $options = $parser->getOptions();
        $this->assertEquals(1, $options['a']);
        $operands = $parser->getOperands();
        //$this->assertCount(0, $operands);
        //$this->assertEquals('b', $operands[0]);
    }

    public function testParseOperandsOnly()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT),
            new Option('b', null)
        ));
        $parser->parse('-- -a -b');

        $this->assertCount(0, $parser->getOptions());
        $operands = $parser->getOperands();
        $this->assertCount(2, $operands);
        $this->assertEquals('-a', $operands[0]);
        $this->assertEquals('-b', $operands[1]);
    }

    public function testParseLongOptionWithoutArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $parser->parse('--option');

        $options = $parser->getOptions();
        $this->assertEquals(1, $options['option']);
    }

    public function testParseLongOptionWithoutArgumentAndOperand()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::NO_ARGUMENT)
        ));
        $parser->parse('--option something');

        $options = $parser->getOptions();
        $this->assertEquals(1, $options['option']);
        $operands = $parser->getOperands();
        //$this->assertCount(0, $operands);
    }

    public function testParseLongOptionWithArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $parser->parse('--option=value');

        $options = $parser->getOptions();
        $this->assertEquals('value', $options['option']);
        $this->assertEquals('value', $options['o']);
    }

    public function testParseLongOptionWithEqualsSignAndArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $parser->parse('--option=value something');

        $options = $parser->getOptions();
        $this->assertEquals('value', $options['option']);
        $operands = $parser->getOperands();
        //$this->assertCount(1, $operands);
        //$this->assertEquals('something', $operands[0]);
    }

    public function testParseLongOptionWithValueStartingWithHyphen()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::REQUIRED_ARGUMENT)
        ));
        $parser->parse('--option=-value');

        $options = $parser->getOptions();
        $this->assertEquals('-value', $options['option']);
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
        $parser->parse('-a -b');

        $options = $parser->getOptions();
        $this->assertEquals(1, $options['a']);
        $this->assertEquals(1, $options['b']);
    }

    public function testParseOptionWithDefaultValue()
    {
        $optionA = new Option('a', null, Getopt::REQUIRED_ARGUMENT);
        $optionA->setArgument(new Argument(10));
        $optionB = new Option('b', 'beta', Getopt::REQUIRED_ARGUMENT);
        $optionB->setArgument(new Argument(20));
        $parser = new CommandLineParser(array($optionA, $optionB));
        $parser->parse('--beta=12');

        $options = $parser->getOptions();
        $this->assertEquals(12, $options['beta']);
       // $this->assertEquals(20, $options['b']);
       // $this->assertEquals(20, $options['beta']);
    }

    public function testDoubleHyphenNotInOperands()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::NO_ARGUMENT)
        ));
        $parser->parse('-a -- bar baz');

        $options = $parser->getOptions();
        $this->assertEquals('1', $options['a']);
        $operands = $parser->getOperands();
        $this->assertCount(2, $operands);
        //$this->assertEquals('foo', $operands[0]);
        //$this->assertEquals('bar', $operands[1]);
        //$this->assertEquals('baz', $operands[2]);
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
        $parser->parse('-a 1 -b 2 -c');

        $options = $parser->getOptions();
        $this->assertSame(1, $options['a']);
    }

    public function testParseInvalidArgument()
    {
/*
        $this->setExpectedException('UnexpectedValueException');

        $option = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
        $option->setArgument(new Argument(null, null));
        $parser = new CommandLineParser(array($option));
        $parser->parse('-a nonnumeric');
        //$this->assertNotEquals(1, 2);
*/
    }
    
    //@group quirks-_mode
    public function testQuirksModeUndefinedOption()
    {
      $parser = new CommandLineParser(array(
          new Option('a', null)
      ));
     // $parser->setQuirksMode(Getopt::QUIRKS_MODE_ALLOW_UNDEFINED_OPTIONS, true);
      $parser->parse('-a');      
    }

    //@group quirks-mode
    public function testQuirksModeEnabled() {
      $this->setExpectedException('UnexpectedValueException');
      $parser = new CommandLineParser(array(
          new Option('a', null)
      ));
      //$parser->setQuirksMode(Getopt::QUIRKS_MODE_ALLOW_UNDEFINED_OPTIONS, false);
      $parser->parse('-b');
    }
}
