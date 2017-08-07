<?php

namespace GetOpt\Test\Operands;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;
use PHPUnit\Framework\TestCase;

class CommonTest extends TestCase
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
}
