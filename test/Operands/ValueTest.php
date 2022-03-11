<?php

namespace GetOpt\Test\Operands;

use GetOpt\Operand;
use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{
    /** @test */
    public function toStringWithoutValue(): void
    {
        $operand = Operand::create('file');

        self::assertSame('', (string)$operand);
    }

    /** @test */
    public function toStringWithDefaultValue(): void
    {
        $operand = Operand::create('file')
            ->setDefaultValue('/dev/random');

        self::assertSame('/dev/random', (string)$operand);
    }

    /** @test */
    public function toStringWithValue(): void
    {
        $operand = Operand::create('file');

        $operand->setValue('/dev/null');

        self::assertSame('/dev/null', (string)$operand);
    }

    /** @test */
    public function toStringWithMultipleValue(): void
    {
        $operand = Operand::create('files', Operand::MULTIPLE);

        $operand->setValue('/dev/null');
        $operand->setValue('/dev/random');

        self::assertSame('/dev/null,/dev/random', (string)$operand);
    }
}
