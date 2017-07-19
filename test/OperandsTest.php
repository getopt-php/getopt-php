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
}
