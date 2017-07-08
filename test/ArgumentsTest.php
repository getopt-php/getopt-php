<?php

namespace GetOpt;

use PHPUnit\Framework\TestCase;

class ArgumentsTest extends TestCase
{
    /** @var Getopt */
    protected $getopt;

    protected function setUp()
    {
        $this->getopt = new Getopt();
    }

    public function testParseNoOptions()
    {
        $this->getopt->process(Arguments::fromString('something'));

        $this->assertCount(0, $this->getopt->getOptions());
        $operands = $this->getopt->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('something', $operands[0]);
    }

    public function testParseUnknownOption()
    {
        $this->setExpectedException('UnexpectedValueException');
        $this->getopt->addOption(new Option('a', null));

        $this->getopt->process('-b');
    }

    public function testUnknownLongOption()
    {
        $this->setExpectedException('UnexpectedValueException');
        $this->getopt->addOption(new Option('a', 'alpha'));

        $this->getopt->process('--beta');
    }

    public function testParseRequiredArgumentMissing()
    {
        $this->setExpectedException('UnexpectedValueException');
        $this->getopt->addOption(new Option('a', null, Getopt::REQUIRED_ARGUMENT));

        $this->getopt->process('-a');
    }

    public function testParseMultipleOptionsWithOneHyphen()
    {
        $this->getopt->addOptions(array(
            new Option('a'),
            new Option('b'),
        ));

        $this->getopt->process('-ab');

        $options = $this->getopt->getOptions();
        $this->assertEquals(1, $options['a']);
        $this->assertEquals(1, $options['b']);
    }

    public function testParseCumulativeOption()
    {
        $this->getopt->addOptions(array(
            new Option('a'),
            new Option('b'),
        ));

        $this->getopt->process('-a -b -a -a');

        $options = $this->getopt->getOptions();
        $this->assertEquals(3, $options['a']);
        $this->assertEquals(1, $options['b']);
    }

    public function testParseCumulativeOptionShort()
    {
        $this->getopt->addOptions(array(
            new Option('a'),
            new Option('b'),
        ));

        $this->getopt->process('-abaa');

        $options = $this->getopt->getOptions();
        $this->assertEquals(3, $options['a']);
        $this->assertEquals(1, $options['b']);
    }

    public function testParseShortOptionWithArgument()
    {
        $this->getopt->addOptions(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));

        $this->getopt->process('-a value');

        $options = $this->getopt->getOptions();
        $this->assertEquals('value', $options['a']);
    }

    public function testParseZeroArgument()
    {
        $this->getopt->addOptions(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));

        $this->getopt->process('-a 0');

        $options = $this->getopt->getOptions();
        $this->assertEquals('0', $options['a']);
    }

    public function testParseNumericOption()
    {
        $this->getopt->addOptions(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT),
            new Option('2', null)
        ));

        $this->getopt->process('-a 2 -2');

        $options = $this->getopt->getOptions();
        $this->assertEquals('2', $options['a']);
        $this->assertEquals(1, $options['2']);
    }

    public function testParseCollapsedShortOptionsRequiredArgumentMissing()
    {
        $this->setExpectedException('UnexpectedValueException');
        $this->getopt->addOptions(array(
            new Option('a', null),
            new Option('b', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $this->getopt->process('-ab');
    }

    public function testParseCollapsedShortOptionsWithArgument()
    {
        $this->getopt->addOptions(array(
            new Option('a', null),
            new Option('b', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $this->getopt->process('-ab value');

        $options = $this->getopt->getOptions();
        $this->assertEquals(1, $options['a']);
        $this->assertEquals('value', $options['b']);
    }

    public function testParseNoArgumentOptionAndOperand()
    {
        $this->getopt->addOptions(array(
            new Option('a', null),
        ));
        $this->getopt->process('-a b');

        $options = $this->getopt->getOptions();
        $this->assertEquals(1, $options['a']);
        $operands = $this->getopt->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('b', $operands[0]);
    }

    public function testParsedRequiredArgumentWithNoSpace()
    {
        $this->getopt->addOptions(array(
            new Option('p', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $this->getopt->process('-ppassword');
        $options = $this->getopt->getOptions();
        $this->assertEquals('password', $options['p']);
    }
    public function testParseCollapsedRequiredArgumentWithNoSpace()
    {
        $this->getopt->addOptions(array(
            new Option('v', null),
            new Option('p', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $this->getopt->process('-vvvppassword');
        $options = $this->getopt->getOptions();
        $this->assertEquals('password', $options['p']);
        $this->assertEquals(3, $options['v']);
    }

    public function testParseOperandsOnly()
    {
        $this->getopt->addOptions(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT),
            new Option('b', null)
        ));
        $this->getopt->process('-- -a -b');

        $this->assertCount(0, $this->getopt->getOptions());
        $operands = $this->getopt->getOperands();
        $this->assertCount(2, $operands);
        $this->assertEquals('-a', $operands[0]);
        $this->assertEquals('-b', $operands[1]);
    }

    public function testParseLongOptionWithoutArgument()
    {
        $this->getopt->addOptions(array(
            new Option('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $this->getopt->process('--option');

        $options = $this->getopt->getOptions();
        $this->assertEquals(1, $options['option']);
    }

    public function testParseLongOptionWithoutArgumentAndOperand()
    {
        $this->getopt->addOptions(array(
            new Option('o', 'option', Getopt::NO_ARGUMENT)
        ));
        $this->getopt->process('--option something');

        $options = $this->getopt->getOptions();
        $this->assertEquals(1, $options['option']);
        $operands = $this->getopt->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('something', $operands[0]);
    }

    public function testParseLongOptionWithArgument()
    {
        $this->getopt->addOptions(array(
            new Option('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $this->getopt->process('--option value');

        $options = $this->getopt->getOptions();
        $this->assertEquals('value', $options['option']);
        $this->assertEquals('value', $options['o']);
    }

    public function testParseLongOptionWithEqualsSignAndArgument()
    {
        $this->getopt->addOptions(array(
            new Option('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $this->getopt->process('--option=value something');

        $options = $this->getopt->getOptions();
        $this->assertEquals('value', $options['option']);
        $operands = $this->getopt->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('something', $operands[0]);
    }

    public function testParseLongOptionWithValueStartingWithHyphen()
    {
        $this->getopt->addOptions(array(
            new Option('o', 'option', Getopt::REQUIRED_ARGUMENT)
        ));
        $this->getopt->process('--option=-value');

        $options = $this->getopt->getOptions();
        $this->assertEquals('-value', $options['option']);
    }

    public function testParseNoValueStartingWithHyphenRequired()
    {
        $this->setExpectedException('UnexpectedValueException');
        $this->getopt->addOptions(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT),
            new Option('b', null)
        ));
        $this->getopt->process('-a -b');
    }

    public function testParseNoValueStartingWithHyphenOptional()
    {
        $this->getopt->addOptions(array(
            new Option('a', null, Getopt::OPTIONAL_ARGUMENT),
            new Option('b', null)
        ));
        $this->getopt->process('-a -b');

        $options = $this->getopt->getOptions();
        $this->assertEquals(1, $options['a']);
        $this->assertEquals(1, $options['b']);
    }

    public function testParseOptionWithDefaultValue()
    {
        $optionA = new Option('a', null, Getopt::REQUIRED_ARGUMENT);
        $optionA->setArgument(new Argument(10));
        $optionB = new Option('b', 'beta', Getopt::REQUIRED_ARGUMENT);
        $optionB->setArgument(new Argument(20));
        $this->getopt->addOptions(array($optionA, $optionB));
        $this->getopt->process('-a 12');

        $options = $this->getopt->getOptions();
        $this->assertEquals(12, $options['a']);
        $this->assertEquals(20, $options['b']);
        $this->assertEquals(20, $options['beta']);
    }

    public function testMultipleArgumentOptions()
    {
        $this->getopt->addOption(new Option('a', null, Getopt::MULTIPLE_ARGUMENT));

        $this->getopt->process('-a value1 -a value2');

        $this->assertEquals(['value1', 'value2'], $this->getopt->getOption('a'));
    }

    public function testDoubleHyphenNotInOperands()
    {
        $this->getopt->addOptions(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $this->getopt->process('-a 0 foo -- bar baz');

        $options = $this->getopt->getOptions();
        $this->assertEquals('0', $options['a']);
        $operands = $this->getopt->getOperands();
        $this->assertCount(3, $operands);
        $this->assertEquals('foo', $operands[0]);
        $this->assertEquals('bar', $operands[1]);
        $this->assertEquals('baz', $operands[2]);
    }

    public function testSingleHyphenValue()
    {
        $this->getopt->addOptions(array(
            new Option('a', 'alpha', Getopt::REQUIRED_ARGUMENT)
        ));

        $this->getopt->process('-a -');

        $options = $this->getopt->getOptions();
        $this->assertEquals('-', $options['a']);
        $operands = $this->getopt->getOperands();
        $this->assertCount(0, $operands);

        $this->getopt->process('--alpha -');

        $options = $this->getopt->getOptions();
        $this->assertEquals('-', $options['a']);
        $operands = $this->getopt->getOperands();
        $this->assertCount(0, $operands);
    }

    public function testSingleHyphenOperand()
    {
        $this->getopt->addOptions(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $this->getopt->process('-a 0 -');

        $options = $this->getopt->getOptions();
        $this->assertEquals('0', $options['a']);
        $operands = $this->getopt->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('-', $operands[0]);
    }

    public function testOptionsAfterOperands()
    {
        $this->getopt->addOptions(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT),
            new Option('b', null, Getopt::REQUIRED_ARGUMENT)
        ));

        $this->getopt->process('-a 42 operand -b "don\'t panic"');

        $this->assertEquals(array(
            'a' => 42,
            'b' => 'don\'t panic'
        ), $this->getopt->getOptions());
        $this->assertEquals(array('operand'), $this->getopt->getOperands());
    }

    public function testEmptyOperandsAndOptionsWithString()
    {
        $this->getopt->addOptions(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));

        $this->getopt->process('-a "" ""');

        $this->assertSame(array('a' => ''), $this->getopt->getOptions());
        $this->assertSame(array(''), $this->getopt->getOperands());
    }

    public function testEmptyOperandsAndOptionsWithArray()
    {
        $this->getopt->addOptions(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));

        // this is how we get it in $_SERVER['argv']
        $this->getopt->process(array(
            '-a',
            '',
            ''
        ));

        $this->assertSame(array('a' => ''), $this->getopt->getOptions());
        $this->assertSame(array(''), $this->getopt->getOperands());
    }

    public function testSpaceOperand()
    {
        $this->getopt->addOptions(array());

        $this->getopt->process('" "');

        $this->assertSame(array(' '), $this->getopt->getOperands());
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
        $this->getopt->addOptions(array($optionA, $optionB, $optionC));
        $this->getopt->process('-a 1 -b 2 -c');

        $options = $this->getopt->getOptions();
        $this->assertSame('1', $options['a']);
        $this->assertSame('2', $options['b']);
        $this->assertSame(1, $options['c']);
    }

    public function testParseInvalidArgument()
    {
        $this->setExpectedException('UnexpectedValueException');
        $validation = 'is_numeric';
        $option = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
        $option->setArgument(new Argument(null, $validation));
        $this->getopt->addOptions(array($option));
        $this->getopt->process('-a nonnumeric');
    }

    public function testStringWithSingleQuotes()
    {
        $this->getopt->addOptions(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
        ));

        $this->getopt->process('-a \'the value\'');
        $options = $this->getopt->getOptions();

        self::assertSame('the value', $options['a']);
    }

    public function testStringWithDoubleQuotes()
    {
        $this->getopt->addOptions(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
        ));

        $this->getopt->process('-a "the value"');
        $options = $this->getopt->getOptions();

        self::assertSame('the value', $options['a']);
    }

    public function testSingleQuotesInString()
    {
        $this->getopt->addOptions(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
        ));

        $this->getopt->process('-a "the \'"');
        $options = $this->getopt->getOptions();

        self::assertSame('the \'', $options['a']);
    }

    public function testDoubleQuotesInString()
    {
        $this->getopt->addOptions(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
        ));

        $this->getopt->process('-a \'the "\'');
        $options = $this->getopt->getOptions();

        self::assertSame('the "', $options['a']);
    }

    public function testQuoteConcatenation()
    {
        $this->getopt->addOptions(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
            new Option('b', 'optB', Getopt::REQUIRED_ARGUMENT),
        ));

        $this->getopt->process('-a \'this uses \'"\'"\' inside single quote\' -b "this uses "\'"\'" inside double quote"');
        $options = $this->getopt->getOptions();

        self::assertSame('this uses \' inside single quote', $options['a']);
        self::assertSame('this uses " inside double quote', $options['b']);
    }

    public function testLinefeedAsSeparator()
    {
        $this->getopt->addOptions(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
        ));

        $this->getopt->process("-a\nvalue");
        $options = $this->getopt->getOptions();

        self::assertSame('value', $options['a']);
    }

    public function testTabAsSeparator()
    {
        $this->getopt->addOptions(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
        ));

        $this->getopt->process("-a\tvalue");
        $options = $this->getopt->getOptions();

        self::assertSame('value', $options['a']);
    }
}
