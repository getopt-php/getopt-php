<?php

namespace GetOpt;

use PHPUnit\Framework\TestCase;

class OperandsTest extends TestCase
{
    public function testAddOperands()
    {
        $operand1 = new Operand('op1');
        $operand2 = new Operand('op2');
        $operand3 = new Operand('op3');

        $getopt = new Getopt();
        $getopt->addOperand($operand1);
        $getopt->addOperands([$operand2, $operand3]);

        self::assertSame([$operand1, $operand2, $operand3], $getopt->getOperands(true));
    }

    public function testOperandValidation()
    {
        $operand = new Operand('op1', false, null, function ($value) {
            return false; // this validator is always false
        });

        $getopt = new Getopt();
        $getopt->addOperand($operand);

        $this->setExpectedException('UnexpectedValueException');
        $getopt->process('"any value"');
    }

    public function testOptionalOperand()
    {
        $operand = new Operand('op1', false); // false is default

        $getopt = new Getopt();
        $getopt->addOperand($operand);
        $getopt->process('');

        self::assertSame([], $getopt->getOperands());
    }

    public function testRequiredOperand()
    {
        $operand = new Operand('op1', true);

        $getopt = new Getopt();
        $getopt->addOperand($operand);

        $this->setExpectedException('UnexpectedValueException');
        $getopt->process('');
    }

    public function testGetOperandByName()
    {
        $operand = new Operand('op1');

        $getopt = new Getopt();
        $getopt->addOperand($operand);
        $getopt->process('42');

        self::assertSame('42', $getopt->getOperand('op1'));
    }

    public function testGetOperandByNameThrows()
    {
        $getopt = new Getopt();
        $getopt->process('42');

        $this->setExpectedException('InvalidArgumentException');
        $getopt->getOperand('op1');
    }

    public function testDefaultValue()
    {
        $operand = new Operand('op1', false, 42);

        $getopt = new Getopt();
        $getopt->addOperand($operand);
        $getopt->process('');

        self::assertSame(42, $getopt->getOperand('op1'));
    }

    public function testAllPreviousOperandsGetRequiredToo()
    {
        $operand1 = new Operand('op1', false); // this is not required
        $operand2 = new Operand('op2', true);

        $getopt = new Getopt();
        $getopt->addOperands([$operand1, $operand2]);

        self::assertTrue($getopt->getOperands(true)[0]->isRequired());
    }

    public function testCommandsCanHaveOperands()
    {
        $operand = new Operand('op1');
        $command = new Command('command1', 'Command 1', 'var_dump');
        $command->addOperands([$operand]);

        self::assertSame([$operand], $command->getOperands());
    }

    public function testCommandWithOperand()
    {
        $getopt = new Getopt();
        $command = new Command('command', 'This is any command', 'var_dump');
        $operand = new Operand('file');
        $command->addOperand($operand);
        $getopt->addCommand($command);

        $getopt->parse('command path/to/file');

        self::assertSame('path/to/file', $getopt->getOperand('file'));
    }

    public function testHelpContainsOperandNames()
    {
        $operand1 = new Operand('op1', true);
        $operand2 = new Operand('op2', false);
        $script = $_SERVER['PHP_SELF'];

        $getopt = new Getopt();
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

        $getopt = new Getopt();
        $command = new Command('command', 'This is any command', 'var_dump');
        $command->addOperands([$operand1, $operand2]);
        $getopt->addCommand($command);

        try {
            $getopt->parse('command');
        } catch (MissingArgumentException $exception) {
        }

        self::assertSame(
            'Usage: /tmp/ide-phpunit.php command <op1> [<op2>] [operands]' . PHP_EOL . PHP_EOL .
            'This is any command' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText()
        );
    }
}
