<?php

namespace GetOpt\Test;

use GetOpt\Argument;
use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase
{
    /** @test */
    public function constructor(): void
    {
        $argument1 = new Argument();
        $argument2 = new Argument(10);
        self::assertFalse($argument1->hasDefaultValue());
        self::assertSame(10, $argument2->getDefaultValue());
    }

    /** @test */
    public function setDefaultValueNotScalar(): void
    {
        self::expectException(\InvalidArgumentException::class);
        $argument = new Argument();
        $argument->setDefaultValue([]);
    }

    /** @test */
    public function validates(): void
    {
        $test     = $this;
        $argument = new Argument();
        $argument->setValidation(
            function ($arg) use ($test, $argument) {
                $test->assertSame('test', $arg);
                return true;
            }
        );
        self::assertTrue($argument->hasValidation());
        self::assertTrue($argument->validates('test'));
    }

    /** @test */
    public function falsyDefaultValue(): void
    {
        $argument = new Argument('');

        self::assertTrue($argument->hasDefaultValue());
    }
}
