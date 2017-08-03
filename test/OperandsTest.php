<?php

namespace GetOpt;

use GetOpt\ArgumentException\Missing;
use PHPUnit\Framework\TestCase;

class OperandsTest extends TestCase
{
    public function testOperandsAreResetted()
    {
        $getopt = new GetOpt();
        $getopt->process('"any operand"');

        $getopt->process('');

        self::assertSame([], $getopt->getOperands());
    }

    public function testAddOperands()
    {
        $operand1 = new Operand('op1');
        $operand2 = new Operand('op2');
        $operand3 = new Operand('op3');

        $getopt = new GetOpt();
        $getopt->addOperand($operand1);
        $getopt->addOperands([$operand2, $operand3]);

        self::assertSame([$operand1, $operand2, $operand3], $getopt->getOperandObjects());
    }

    public function testOperandValidation()
    {
        $operand = Operand::create('op1')
            ->setValidation(function ($value) {
                return $value === null; // this validator is always false
            });

        $getopt = new GetOpt();
        $getopt->addOperand($operand);

        $this->setExpectedException('GetOpt\ArgumentException\Invalid');
        $getopt->process('"any value"');
    }

    public function testOptionalOperand()
    {
        $operand = new Operand('op1', Operand::OPTIONAL); // false is default

        $getopt = new GetOpt();
        $getopt->addOperand($operand);
        $getopt->process('');

        self::assertSame([], $getopt->getOperands());
    }

    public function testRequiredOperand()
    {
        $operand = new Operand('op1', Operand::REQUIRED);

        $getopt = new GetOpt();
        $getopt->addOperand($operand);

        $this->setExpectedException('GetOpt\ArgumentException\Missing');
        $getopt->process('');
    }

    public function testGetOperandByName()
    {
        $operand = new Operand('op1');

        $getopt = new GetOpt();
        $getopt->addOperand($operand);
        $getopt->process('42');

        self::assertSame('42', $getopt->getOperand('op1'));
    }

    public function testGetOperandByNameThrows()
    {
        $getopt = new GetOpt();
        $getopt->addOperand(new Operand('any'));
        $getopt->process('42');

        $this->setExpectedException('InvalidArgumentException');
        $getopt->getOperand('op1');
    }

    public function testDefaultValue()
    {
        $operand = Operand::create('op1')
            ->setDefaultValue(42);

        $getopt = new GetOpt();
        $getopt->addOperand($operand);
        $getopt->process('');

        self::assertSame(42, $getopt->getOperand('op1'));
    }

    public function testAllPreviousOperandsGetRequiredToo()
    {
        $operand1 = new Operand('op1', Operand::OPTIONAL);
        $operand2 = new Operand('op2', Operand::REQUIRED);

        $getopt = new GetOpt();
        $getopt->addOperands([$operand1, $operand2]);

        self::assertTrue($getopt->getOperandObjects()[0]->isRequired());
    }

    public function testCommandsCanHaveOperands()
    {
        $operand = new Operand('op1');
        $command = new Command('command1', 'var_dump');
        $command->addOperands([$operand]);

        self::assertSame([$operand], $command->getOperands());
    }

    public function testCommandWithOperand()
    {
        $getopt = new GetOpt();
        $command = new Command('command', 'var_dump');
        $operand = new Operand('file');
        $command->addOperand($operand);
        $getopt->addCommand($command);

        $getopt->process('command path/to/file');

        self::assertSame('path/to/file', $getopt->getOperand('file'));
    }

    public function testHelpContainsOperandNames()
    {
        $operand1 = new Operand('op1', true);
        $operand2 = new Operand('op2', false);
        $script = $_SERVER['PHP_SELF'];

        $getopt = new GetOpt();
        $getopt->addOperands([$operand1, $operand2]);

        self::assertSame(
            'Usage: ' . $script . ' <op1> [<op2>] [operands]' . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    public function testHelpCommandDefinesOperands()
    {
        $operand1 = new Operand('op1', true);
        $operand2 = new Operand('op2', false);
        $script = $_SERVER['PHP_SELF'];

        $getopt = new GetOpt();
        $command = Command::create('command', 'var_dump')->setDescription('This is any command');
        $command->addOperands([$operand1, $operand2]);
        $getopt->addCommand($command);

        try {
            $getopt->process('command');
        } catch (Missing $exception) {
        }

        self::assertSame(
            'Usage: ' . $script . ' command <op1> [<op2>] [operands]' . PHP_EOL . PHP_EOL .
            'This is any command' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    // multiple operands

    public function testValueForMultiple()
    {
        $operand1 = new Operand('op1', Operand::OPTIONAL);
        $operand2 = new Operand('op2', Operand::MULTIPLE);

        $getopt = new GetOpt();
        $getopt->addOperands([$operand1, $operand2]);
        $getopt->process('a b c');

        self::assertSame('a', $getopt->getOperand('op1'));
        self::assertSame(['b', 'c'], $getopt->getOperand('op2'));
        self::assertSame(['a', 'b', 'c'], $getopt->getOperands());
    }

    public function testDefaultValueForMultiple()
    {
        $operand = Operand::create('op1', Operand::MULTIPLE)
            ->setDefaultValue(42);

        $getopt = new GetOpt();
        $getopt->addOperand($operand);
        $getopt->process('');

        self::assertSame([42], $getopt->getOperand('op1'));
    }

    public function testRequiredMultiple()
    {
        $operand = new Operand('op1', true, null, null, true);

        $getopt = new GetOpt();
        $getopt->addOperand($operand);

        $this->setExpectedException('GetOpt\ArgumentException\Missing');
        $getopt->process('');
    }

    public function testRequiredMultipleNotToThrow()
    {
        $operand = new Operand('op1', Operand::REQUIRED + Operand::MULTIPLE);

        $getopt = new GetOpt();
        $getopt->addOperand($operand);
        $getopt->process('42');

        self::assertSame(['42'], $getopt->getOperand('op1'));
    }

    public function testValidationOfMultiple()
    {
        $operand1 = Operand::create('op1', Operand::MULTIPLE)
            ->setValidation(function ($value) {
                return $value <= 42;
            });

        $getopt = new GetOpt();
        $getopt->addOperand($operand1);

        $this->setExpectedException('GetOpt\ArgumentException\Invalid');
        $getopt->process('42 43');
    }

    public function testRestrictsAddingAfterMultiple()
    {
        $operand1 = new Operand('op1', Operand::MULTIPLE);
        $operand2 = new Operand('op2', Operand::OPTIONAL);

        $getopt = new GetOpt();
        $getopt->addOperand($operand1);

        $this->setExpectedException('InvalidArgumentException');
        $getopt->addOperand($operand2);
    }

    public function testHelpTextForMultiple()
    {
        $operand = new Operand('op1', Operand::MULTIPLE);
        $script = $_SERVER['PHP_SELF'];

        $getopt = new GetOpt();
        $getopt->addOperand($operand);

        self::assertSame(
            'Usage: ' . $script . ' [<op1>] [<op1>...]' . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    public function testHelpTextForRequiredMultiple()
    {
        $operand = new Operand('op1', Operand::MULTIPLE + Operand::REQUIRED);
        $script = $_SERVER['PHP_SELF'];

        $getopt = new GetOpt();
        $getopt->addOperand($operand);

        self::assertSame(
            'Usage: ' . $script . ' <op1> [<op1>...]' . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    // strict operands

    public function testNoOperandsAllowed()
    {
        $getopt = new GetOpt();
        $getopt->set(GetOpt::SETTING_STRICT_OPERANDS, true);

        $this->setExpectedException('GetOpt\ArgumentException\Unexpected');
        $getopt->process('"some operand"');
    }

    public function testSpecifiedOperandsAllowed()
    {
        $getopt = new GetOpt();
        $getopt->set(GetOpt::SETTING_STRICT_OPERANDS, true);

        $getopt->addOperand(new Operand('op1'));
        $getopt->process('"some operand"');

        self::assertSame('some operand', $getopt->getOperand('op1'));
    }

    public function testHelpDoesNotShowAdditionalOperands()
    {
        $getopt = new GetOpt();
        $getopt->set(GetOpt::SETTING_STRICT_OPERANDS, true);
        $getopt->addOperand(new Operand('file', true));
        $script = $_SERVER['PHP_SELF'];

        self::assertSame(
            'Usage: ' . $script . ' <file> ' . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    // __toString

    public function testToStringWithoutValue()
    {
        $operand = Operand::create('file');

        self::assertSame('', (string)$operand);
    }

    public function testToStringWithDefaultValue()
    {
        $operand = Operand::create('file')
            ->setDefaultValue('/dev/random');

        self::assertSame('/dev/random', (string)$operand);
    }

    public function testToStringWithValue()
    {
        $operand = Operand::create('file');

        $operand->setValue('/dev/null');

        self::assertSame('/dev/null', (string)$operand);
    }

    public function testToStringWithMultipleValue()
    {
        $operand = Operand::create('files', Operand::MULTIPLE);

        $operand->setValue('/dev/null');
        $operand->setValue('/dev/random');

        self::assertSame('/dev/null,/dev/random', (string)$operand);
    }
}
