<?php

namespace GetOpt\Test;

use GetOpt\Argument;
use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase
{
    /** @test */
    public function constructor()
    {
        $argument1 = new Argument();
        $argument2 = new Argument(10);
        $this->assertFalse($argument1->hasDefaultValue());
        $this->assertEquals(10, $argument2->getDefaultValue());
    }

    /** @test */
    public function setDefaultValueNotScalar()
    {
        $this->setExpectedException('InvalidArgumentException');
        $argument = new Argument();
        $argument->setDefaultValue([]);
    }

    /** @test */
    public function validates()
    {
        $test     = $this;
        $argument = new Argument();
        $argument->setValidation(
            function ($arg) use ($test, $argument) {
                $test->assertEquals('test', $arg);
                return true;
            }
        );
        $this->assertTrue($argument->hasValidation());
        $this->assertTrue($argument->validates('test'));
    }

    /** @test */
    public function doesNotValidateWithoutCustomMessage()
    {
        $test = $this;
        $argument = new Argument();
        $argument->setValidation(
            function ($arg, $validator) use ($test, $argument) {
                $test->assertNotEquals('notthis', $arg);
            }
        );
        $this->assertFalse($argument->validates('test'));
        $this->assertEquals('Option \'arg\' has an invalid value', $argument->getValidationMessage());
    }

    /** @test */
    public function doesNotValidateWithCustomMessage()
    {
        $test = $this;
        $argument = new Argument();
        $argument->setValidation(
            function ($arg, $validator) use ($test, $argument) {
                $test->assertNotEquals('notthis', $arg);
            },
            'Custom message: %s'
        );
        $this->assertFalse($argument->validates('test'));
        $this->assertEquals('Custom message: arg', $argument->getValidationMessage());
    }

    /** @test */
    public function doesNotValidateWithCustomMessageInClosure()
    {
        $test = $this;
        $argument = new Argument();
        $argument->setValidation(
            function ($arg, $validator) use ($test, $argument) {
                $test->assertNotEquals('notthis', $arg);
                $validator->setMessage('Custom message: %s');
            }
        );
        $this->assertFalse($argument->validates('test'));
        $this->assertEquals('Custom message: arg', $argument->getValidationMessage());
    }

    /** @test */
    public function falsyDefaultValue()
    {
        $argument = new Argument('');

        self::assertTrue($argument->hasDefaultValue());
    }
}
