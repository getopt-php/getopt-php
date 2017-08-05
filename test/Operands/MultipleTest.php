<?php

namespace GetOpt\Test\Operands;

use GetOpt\GetOpt;
use GetOpt\Operand;
use PHPUnit\Framework\TestCase;

class MultipleTest extends TestCase
{
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
}
