<?php

namespace GetOpt;

use PHPUnit\Framework\TestCase;

class ArgumentsTest extends TestCase
{
    /** @var GetOpt */
    protected $getopt;

    protected function setUp()
    {
        $this->getopt = new GetOpt();
    }

    public function testParseNoOptions()
    {
        $this->getopt->process(Arguments::fromString('something'));

        self::assertCount(0, $this->getopt->getOptions());
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertEquals('something', $operands[0]);
    }

    public function testParseUnknownOption()
    {
        $this->setExpectedException('GetOpt\ArgumentException\Unexpected');
        $this->getopt->addOption(new Option('a', null));

        $this->getopt->process('-b');
    }

    public function testUnknownLongOption()
    {
        $this->setExpectedException('GetOpt\ArgumentException\Unexpected');
        $this->getopt->addOption(new Option('a', 'alpha'));

        $this->getopt->process('--beta');
    }

    public function testParseRequiredArgumentMissing()
    {
        $this->setExpectedException('GetOpt\ArgumentException\Missing');
        $this->getopt->addOption(new Option('a', null, GetOpt::REQUIRED_ARGUMENT));

        $this->getopt->process('-a');
    }

    public function testParseMultipleOptionsWithOneHyphen()
    {
        $this->getopt->addOptions([
            new Option('a'),
            new Option('b'),
        ]);

        $this->getopt->process('-ab');

        $options = $this->getopt->getOptions();
        self::assertEquals(1, $options['a']);
        self::assertEquals(1, $options['b']);
    }

    public function testParseCumulativeOption()
    {
        $this->getopt->addOptions([
            new Option('a'),
            new Option('b'),
        ]);

        $this->getopt->process('-a -b -a -a');

        $options = $this->getopt->getOptions();
        self::assertEquals(3, $options['a']);
        self::assertEquals(1, $options['b']);
    }

    public function testParseCumulativeOptionShort()
    {
        $this->getopt->addOptions([
            new Option('a'),
            new Option('b'),
        ]);

        $this->getopt->process('-abaa');

        $options = $this->getopt->getOptions();
        self::assertEquals(3, $options['a']);
        self::assertEquals(1, $options['b']);
    }

    public function testParseShortOptionWithArgument()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a value');

        $options = $this->getopt->getOptions();
        self::assertEquals('value', $options['a']);
    }

    public function testParseZeroArgument()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a 0');

        $options = $this->getopt->getOptions();
        self::assertEquals('0', $options['a']);
    }

    public function testParseNumericOption()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT),
            new Option('2', null)
        ]);

        $this->getopt->process('-a 2 -2');

        $options = $this->getopt->getOptions();
        self::assertEquals('2', $options['a']);
        self::assertEquals(1, $options['2']);
    }

    public function testParseCollapsedShortOptionsRequiredArgumentMissing()
    {
        $this->setExpectedException('GetOpt\ArgumentException\Missing');
        $this->getopt->addOptions([
            new Option('a', null),
            new Option('b', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-ab');
    }

    public function testParseCollapsedShortOptionsWithArgument()
    {
        $this->getopt->addOptions([
            new Option('a', null),
            new Option('b', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-ab value');

        $options = $this->getopt->getOptions();
        self::assertEquals(1, $options['a']);
        self::assertEquals('value', $options['b']);
    }

    public function testParseNoArgumentOptionAndOperand()
    {
        $this->getopt->addOptions([
            new Option('a', null),
        ]);
        $this->getopt->process('-a b');

        $options = $this->getopt->getOptions();
        self::assertEquals(1, $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertEquals('b', $operands[0]);
    }

    public function testParsedRequiredArgumentWithNoSpace()
    {
        $this->getopt->addOptions([
            new Option('p', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-ppassword');
        $options = $this->getopt->getOptions();
        self::assertEquals('password', $options['p']);
    }
    public function testParseCollapsedRequiredArgumentWithNoSpace()
    {
        $this->getopt->addOptions([
            new Option('v', null),
            new Option('p', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-vvvppassword');
        $options = $this->getopt->getOptions();
        self::assertEquals('password', $options['p']);
        self::assertEquals(3, $options['v']);
    }

    public function testParseOperandsOnly()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT),
            new Option('b', null)
        ]);
        $this->getopt->process('-- -a -b');

        self::assertCount(0, $this->getopt->getOptions());
        $operands = $this->getopt->getOperands();
        self::assertCount(2, $operands);
        self::assertEquals('-a', $operands[0]);
        self::assertEquals('-b', $operands[1]);
    }

    public function testParseLongOptionWithoutArgument()
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::OPTIONAL_ARGUMENT)
        ]);
        $this->getopt->process('--option');

        $options = $this->getopt->getOptions();
        self::assertEquals(1, $options['option']);
    }

    public function testParseLongOptionWithoutArgumentAndOperand()
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::NO_ARGUMENT)
        ]);
        $this->getopt->process('--option something');

        $options = $this->getopt->getOptions();
        self::assertEquals(1, $options['option']);
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertEquals('something', $operands[0]);
    }

    public function testParseLongOptionWithArgument()
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::OPTIONAL_ARGUMENT)
        ]);
        $this->getopt->process('--option value');

        $options = $this->getopt->getOptions();
        self::assertEquals('value', $options['option']);
        self::assertEquals('value', $options['o']);
    }

    public function testParseLongOptionWithEqualsSignAndArgument()
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::OPTIONAL_ARGUMENT)
        ]);
        $this->getopt->process('--option=value something');

        $options = $this->getopt->getOptions();
        self::assertEquals('value', $options['option']);
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertEquals('something', $operands[0]);
    }

    public function testParseLongOptionWithValueStartingWithHyphen()
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('--option=-value');

        $options = $this->getopt->getOptions();
        self::assertEquals('-value', $options['option']);
    }

    public function testParseNoValueStartingWithHyphenRequired()
    {
        $this->setExpectedException('GetOpt\ArgumentException\Missing');
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT),
            new Option('b', null)
        ]);
        $this->getopt->process('-a -b');
    }

    public function testParseNoValueStartingWithHyphenOptional()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::OPTIONAL_ARGUMENT),
            new Option('b', null)
        ]);
        $this->getopt->process('-a -b');

        $options = $this->getopt->getOptions();
        self::assertEquals(1, $options['a']);
        self::assertEquals(1, $options['b']);
    }

    public function testParseOptionWithDefaultValue()
    {
        $optionA = new Option('a', null, GetOpt::REQUIRED_ARGUMENT);
        $optionA->setArgument(new Argument(10));
        $optionB = new Option('b', 'beta', GetOpt::REQUIRED_ARGUMENT);
        $optionB->setArgument(new Argument(20));
        $this->getopt->addOptions([$optionA, $optionB]);
        $this->getopt->process('-a 12');

        $options = $this->getopt->getOptions();
        self::assertEquals(12, $options['a']);
        self::assertEquals(20, $options['b']);
        self::assertEquals(20, $options['beta']);
    }

    public function testMultipleArgumentOptions()
    {
        $this->getopt->addOption(new Option('a', null, GetOpt::MULTIPLE_ARGUMENT));

        $this->getopt->process('-a value1 -a value2');

        self::assertEquals(['value1', 'value2'], $this->getopt->getOption('a'));
    }

    public function testDoubleHyphenNotInOperands()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-a 0 foo -- bar baz');

        $options = $this->getopt->getOptions();
        self::assertEquals('0', $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(3, $operands);
        self::assertEquals('foo', $operands[0]);
        self::assertEquals('bar', $operands[1]);
        self::assertEquals('baz', $operands[2]);
    }

    public function testSingleHyphenValue()
    {
        $this->getopt->addOptions([
            new Option('a', 'alpha', GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a -');

        $options = $this->getopt->getOptions();
        self::assertEquals('-', $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(0, $operands);

        $this->getopt->process('--alpha -');

        $options = $this->getopt->getOptions();
        self::assertEquals('-', $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(0, $operands);
    }

    public function testSingleHyphenOperand()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-a 0 -');

        $options = $this->getopt->getOptions();
        self::assertEquals('0', $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertEquals('-', $operands[0]);
    }

    public function testOptionsAfterOperands()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT),
            new Option('b', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a 42 operand -b "don\'t panic"');

        self::assertEquals([
            'a' => 42,
            'b' => 'don\'t panic'
        ], $this->getopt->getOptions());
        self::assertEquals(['operand'], $this->getopt->getOperands());
    }

    public function testEmptyOperandsAndOptionsWithString()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a "" ""');

        self::assertSame(['a' => ''], $this->getopt->getOptions());
        self::assertSame([''], $this->getopt->getOperands());
    }

    public function testEmptyOperandsAndOptionsWithArray()
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        // this is how we get it in $_SERVER['argv']
        $this->getopt->process([
            '-a',
            '',
            ''
        ]);

        self::assertSame(['a' => ''], $this->getopt->getOptions());
        self::assertSame([''], $this->getopt->getOperands());
    }

    public function testSpaceOperand()
    {
        $this->getopt->addOptions([]);

        $this->getopt->process('" "');

        self::assertSame([' '], $this->getopt->getOperands());
    }

    public function testParseWithArgumentValidation()
    {
        $validation = 'is_numeric';
        $optionA = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        $optionA->setArgument(new Argument(null, $validation));
        $optionB = new Option('b', null, GetOpt::REQUIRED_ARGUMENT);
        $optionB->setArgument(new Argument(null, $validation));
        $optionC = new Option('c', null, GetOpt::OPTIONAL_ARGUMENT);
        $optionC->setArgument(new Argument(null, $validation));
        $this->getopt->addOptions([$optionA, $optionB, $optionC]);
        $this->getopt->process('-a 1 -b 2 -c');

        $options = $this->getopt->getOptions();
        self::assertSame('1', $options['a']);
        self::assertSame('2', $options['b']);
        self::assertSame(1, $options['c']);
    }

    public function testParseInvalidArgument()
    {
        $this->setExpectedException('GetOpt\ArgumentException\Invalid');
        $validation = 'is_numeric';
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        $option->setArgument(new Argument(null, $validation));
        $this->getopt->addOptions([$option]);
        $this->getopt->process('-a nonnumeric');
    }

    public function testStringWithSingleQuotes()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a \'the value\'');
        $options = $this->getopt->getOptions();

        self::assertSame('the value', $options['a']);
    }

    public function testStringWithDoubleQuotes()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a "the value"');
        $options = $this->getopt->getOptions();

        self::assertSame('the value', $options['a']);
    }

    public function testSingleQuotesInString()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a "the \'"');
        $options = $this->getopt->getOptions();

        self::assertSame('the \'', $options['a']);
    }

    public function testDoubleQuotesInString()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a \'the "\'');
        $options = $this->getopt->getOptions();

        self::assertSame('the "', $options['a']);
    }

    public function testQuoteConcatenation()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
            new Option('b', 'optB', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a \'\'"\'"\' inside single quote\' -b ""\'"\'" inside double quote"');
        $options = $this->getopt->getOptions();

        self::assertSame('\' inside single quote', $options['a']);
        self::assertSame('" inside double quote', $options['b']);
    }

    public function testQuoteEscapingDoubleQuote()
    {
        $this->getopt->process('-- "this \\" is a double quote"');

        self::assertSame('this " is a double quote', $this->getopt->getOperand(0));
    }

    public function testQuoteEscapingSingleQuote()
    {
        $this->getopt->process("-- 'this \\' is a single quote'");

        self::assertSame("this ' is a single quote", $this->getopt->getOperand(0));
    }

    public function testLinefeedAsSeparator()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process("-a\nvalue");
        $options = $this->getopt->getOptions();

        self::assertSame('value', $options['a']);
    }

    public function testTabAsSeparator()
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process("-a\tvalue");
        $options = $this->getopt->getOptions();

        self::assertSame('value', $options['a']);
    }

    public function testExplictArguments()
    {
        $getopt = $this->getopt;
        $this->getopt->addOptions([
            Option::create('a'),
            Option::create('b')->setValidation(function () use ($getopt) {
                return is_null($getopt->getOption('a'));
            })
        ]);

        $this->setExpectedException('GetOpt\ArgumentException\Invalid');
        $this->getopt->process('-a -b');
    }

    public function testUsingCommand()
    {
        $cmd = new Command('test', 'var_dump', [
            new Option('a', 'alpha')
        ]);
        $this->getopt->addCommand($cmd);

        $this->getopt->process('test -a --alpha');

        self::assertSame(2, $this->getopt->getOption('a'));
        self::assertSame($cmd, $this->getopt->getCommand());
    }
}
