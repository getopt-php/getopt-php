<?php

namespace GetOpt\Test\Operands;

use GetOpt\ArgumentException\Missing;
use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;
use PHPUnit\Framework\TestCase;

class HelpTest extends TestCase
{
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
        $command =Command::create('command', 'var_dump')->setDescription('This is any command');
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
}
