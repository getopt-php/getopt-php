<?php

namespace GetOpt\Test\Operands;

use GetOpt\Operand;
use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{
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
