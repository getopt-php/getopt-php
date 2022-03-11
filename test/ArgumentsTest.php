<?php

namespace GetOpt\Test;

use GetOpt\Argument;
use GetOpt\ArgumentException\Invalid;
use GetOpt\ArgumentException\Missing;
use GetOpt\ArgumentException\Unexpected;
use GetOpt\Arguments;
use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class ArgumentsTest extends TestCase
{
    /** @var GetOpt */
    protected $getopt;

    protected function setUp(): void
    {
        $this->getopt = new GetOpt();
    }

    /** @test */
    public function parseNoOptions(): void
    {
        $this->getopt->process(Arguments::fromString('something'));

        self::assertCount(0, $this->getopt->getOptions());
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertSame('something', $operands[0]);
    }

    /** @test */
    public function parseUnknownOption(): void
    {
        self::expectException(Unexpected::class);
        $this->getopt->addOption(new Option('a', null));

        $this->getopt->process('-b');
    }

    /** @test */
    public function unknownLongOption(): void
    {
        self::expectException(Unexpected::class);
        $this->getopt->addOption(new Option('a', 'alpha'));

        $this->getopt->process('--beta');
    }

    /** @test */
    public function parseRequiredArgumentMissing(): void
    {
        self::expectException(Missing::class);
        $this->getopt->addOption(new Option('a', null, GetOpt::REQUIRED_ARGUMENT));

        $this->getopt->process('-a');
    }

    /** @test */
    public function parseMultipleOptionsWithOneHyphen(): void
    {
        $this->getopt->addOptions([
            new Option('a'),
            new Option('b'),
        ]);

        $this->getopt->process('-ab');

        $options = $this->getopt->getOptions();
        self::assertSame(1, $options['a']);
        self::assertSame(1, $options['b']);
    }

    /** @test */
    public function parseCumulativeOption(): void
    {
        $this->getopt->addOptions([
            new Option('a'),
            new Option('b'),
        ]);

        $this->getopt->process('-a -b -a -a');

        $options = $this->getopt->getOptions();
        self::assertSame(3, $options['a']);
        self::assertSame(1, $options['b']);
    }

    /** @test */
    public function parseCumulativeOptionShort(): void
    {
        $this->getopt->addOptions([
            new Option('a'),
            new Option('b'),
        ]);

        $this->getopt->process('-abaa');

        $options = $this->getopt->getOptions();
        self::assertSame(3, $options['a']);
        self::assertSame(1, $options['b']);
    }

    /** @test */
    public function parseShortOptionWithArgument(): void
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a value');

        $options = $this->getopt->getOptions();
        self::assertSame('value', $options['a']);
    }

    /** @test */
    public function parseZeroArgument(): void
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a 0');

        $options = $this->getopt->getOptions();
        self::assertSame('0', $options['a']);
    }

    /** @test */
    public function parseNumericOption(): void
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT),
            new Option('2', null)
        ]);

        $this->getopt->process('-a 2 -2');

        $options = $this->getopt->getOptions();
        self::assertSame('2', $options['a']);
        self::assertSame(1, $options['2']);
    }

    /** @test */
    public function parseCollapsedShortOptionsRequiredArgumentMissing(): void
    {
        self::expectException(Missing::class);
        $this->getopt->addOptions([
            new Option('a', null),
            new Option('b', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-ab');
    }

    /** @test */
    public function parseCollapsedShortOptionsWithArgument(): void
    {
        $this->getopt->addOptions([
            new Option('a', null),
            new Option('b', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-ab value');

        $options = $this->getopt->getOptions();
        self::assertSame(1, $options['a']);
        self::assertSame('value', $options['b']);
    }

    /** @test */
    public function parseNoArgumentOptionAndOperand(): void
    {
        $this->getopt->addOptions([
            new Option('a', null),
        ]);
        $this->getopt->process('-a b');

        $options = $this->getopt->getOptions();
        self::assertSame(1, $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertSame('b', $operands[0]);
    }

    /** @test */
    public function parsedRequiredArgumentWithNoSpace(): void
    {
        $this->getopt->addOptions([
            new Option('p', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-ppassword');
        $options = $this->getopt->getOptions();
        self::assertSame('password', $options['p']);
    }
    /** @test */
    public function parseCollapsedRequiredArgumentWithNoSpace(): void
    {
        $this->getopt->addOptions([
            new Option('v', null),
            new Option('p', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-vvvppassword');
        $options = $this->getopt->getOptions();
        self::assertSame('password', $options['p']);
        self::assertSame(3, $options['v']);
    }

    /** @test */
    public function parseOperandsOnly(): void
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT),
            new Option('b', null)
        ]);
        $this->getopt->process('-- -a -b');

        self::assertCount(0, $this->getopt->getOptions());
        $operands = $this->getopt->getOperands();
        self::assertCount(2, $operands);
        self::assertSame('-a', $operands[0]);
        self::assertSame('-b', $operands[1]);
    }

    /** @test */
    public function parseLongOptionWithoutArgument(): void
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::OPTIONAL_ARGUMENT)
        ]);
        $this->getopt->process('--option');

        $options = $this->getopt->getOptions();
        self::assertSame(1, $options['option']);
    }

    /** @test */
    public function parseLongOptionWithoutArgumentAndOperand(): void
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::NO_ARGUMENT)
        ]);
        $this->getopt->process('--option something');

        $options = $this->getopt->getOptions();
        self::assertSame(1, $options['option']);
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertSame('something', $operands[0]);
    }

    /** @test */
    public function parseLongOptionWithArgument(): void
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::OPTIONAL_ARGUMENT)
        ]);
        $this->getopt->process('--option value');

        $options = $this->getopt->getOptions();
        self::assertSame('value', $options['option']);
        self::assertSame('value', $options['o']);
    }

    /** @test */
    public function parseLongOptionWithEqualsSignAndArgument(): void
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::OPTIONAL_ARGUMENT)
        ]);
        $this->getopt->process('--option=value something');

        $options = $this->getopt->getOptions();
        self::assertSame('value', $options['option']);
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertSame('something', $operands[0]);
    }

    /** @test */
    public function parseLongOptionWithValueStartingWithHyphen(): void
    {
        $this->getopt->addOptions([
            new Option('o', 'option', GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('--option=-value');

        $options = $this->getopt->getOptions();
        self::assertSame('-value', $options['option']);
    }

    /** @test */
    public function parseValueStartingWithHypenRequired(): void
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT),
            new Option('b', null)
        ]);
        $this->getopt->process('-a -b');

        self::assertSame('-b', $this->getopt->getOption('a'));
    }

    /** @test */
    public function parseNoValueStartingWithHyphenOptional(): void
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::OPTIONAL_ARGUMENT),
            new Option('b', null)
        ]);
        $this->getopt->process('-a -b');

        $options = $this->getopt->getOptions();
        self::assertSame(1, $options['a']);
        self::assertSame(1, $options['b']);
    }

    /** @test */
    public function parseOptionWithDefaultValue(): void
    {
        $optionA = new Option('a', null, GetOpt::REQUIRED_ARGUMENT);
        $optionA->setArgument(new Argument(10));
        $optionB = new Option('b', 'beta', GetOpt::REQUIRED_ARGUMENT);
        $optionB->setArgument(new Argument(20));
        $this->getopt->addOptions([$optionA, $optionB]);
        $this->getopt->process('-a 12');

        $options = $this->getopt->getOptions();
        self::assertSame('12', $options['a']);
        self::assertSame(20, $options['b']);
        self::assertSame(20, $options['beta']);
    }

    /** @test */
    public function multipleArgumentOptions(): void
    {
        $this->getopt->addOption(new Option('a', null, GetOpt::MULTIPLE_ARGUMENT));

        $this->getopt->process('-a value1 -a value2');

        self::assertSame(['value1', 'value2'], $this->getopt->getOption('a'));
    }

    /** @test */
    public function doubleHyphenNotInOperands(): void
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-a 0 foo -- bar baz');

        $options = $this->getopt->getOptions();
        self::assertSame('0', $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(3, $operands);
        self::assertSame('foo', $operands[0]);
        self::assertSame('bar', $operands[1]);
        self::assertSame('baz', $operands[2]);
    }

    /** @test */
    public function singleHyphenValue(): void
    {
        $this->getopt->addOptions([
            new Option('a', 'alpha', GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a -');

        $options = $this->getopt->getOptions();
        self::assertSame('-', $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(0, $operands);

        $this->getopt->process('--alpha -');

        $options = $this->getopt->getOptions();
        self::assertSame('-', $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(0, $operands);
    }

    /** @test */
    public function singleHyphenOperand(): void
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);
        $this->getopt->process('-a 0 -');

        $options = $this->getopt->getOptions();
        self::assertSame('0', $options['a']);
        $operands = $this->getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertSame('-', $operands[0]);
    }

    /** @test */
    public function optionsAfterOperands(): void
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT),
            new Option('b', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a 42 operand -b "don\'t panic"');

        self::assertSame([
            'a' => '42',
            'b' => 'don\'t panic'
        ], $this->getopt->getOptions());
        self::assertSame(['operand'], $this->getopt->getOperands());
    }

    /** @test */
    public function emptyOperandsAndOptionsWithString(): void
    {
        $this->getopt->addOptions([
            new Option('a', null, GetOpt::REQUIRED_ARGUMENT)
        ]);

        $this->getopt->process('-a "" ""');

        self::assertSame(['a' => ''], $this->getopt->getOptions());
        self::assertSame([''], $this->getopt->getOperands());
    }

    /** @test */
    public function emptyOperandsAndOptionsWithArray(): void
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

    /** @test */
    public function spaceOperand(): void
    {
        $this->getopt->addOptions([]);

        $this->getopt->process('" "');

        self::assertSame([' '], $this->getopt->getOperands());
    }

    /** @test */
    public function parseWithArgumentValidation(): void
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

    /** @test */
    public function parseInvalidArgument(): void
    {
        self::expectException(Invalid::class);
        $validation = 'is_numeric';
        $option = new Option('a', null, GetOpt::OPTIONAL_ARGUMENT);
        $option->setArgument(new Argument(null, $validation));
        $this->getopt->addOptions([$option]);
        $this->getopt->process('-a nonnumeric');
    }

    /** @test */
    public function stringWithSingleQuotes(): void
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a \'the value\'');
        $options = $this->getopt->getOptions();

        self::assertSame('the value', $options['a']);
    }

    /** @test */
    public function stringWithDoubleQuotes(): void
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a "the value"');
        $options = $this->getopt->getOptions();

        self::assertSame('the value', $options['a']);
    }

    /** @test */
    public function singleQuotesInString(): void
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a "the \'"');
        $options = $this->getopt->getOptions();

        self::assertSame('the \'', $options['a']);
    }

    /** @test */
    public function doubleQuotesInString(): void
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process('-a \'the "\'');
        $options = $this->getopt->getOptions();

        self::assertSame('the "', $options['a']);
    }

    /** @test */
    public function quoteConcatenation(): void
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

    /** @test */
    public function quoteEscapingDoubleQuote(): void
    {
        $this->getopt->process('-- "this \\" is a double quote"');

        self::assertSame('this " is a double quote', $this->getopt->getOperand(0));
    }

    /** @test */
    public function quoteEscapingSingleQuote(): void
    {
        $this->getopt->process("-- 'this \\' is a single quote'");

        self::assertSame("this ' is a single quote", $this->getopt->getOperand(0));
    }

    /** @test */
    public function linefeedAsSeparator(): void
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process("-a\nvalue");
        $options = $this->getopt->getOptions();

        self::assertSame('value', $options['a']);
    }

    /** @test */
    public function tabAsSeparator(): void
    {
        $this->getopt->addOptions([
            new Option('a', 'optA', GetOpt::REQUIRED_ARGUMENT),
        ]);

        $this->getopt->process("-a\tvalue");
        $options = $this->getopt->getOptions();

        self::assertSame('value', $options['a']);
    }

    /** @test */
    public function explictArguments(): void
    {
        $getopt = $this->getopt;
        $this->getopt->addOptions([
            Option::create('a'),
            Option::create('b')->setValidation(function () use ($getopt) {
                return is_null($getopt->getOption('a'));
            })
        ]);

        self::expectException(Invalid::class);
        $this->getopt->process('-a -b');
    }

    /** @test */
    public function usingCommand(): void
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
