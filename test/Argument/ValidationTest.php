<?php

namespace GetOpt\Test\Argument;

use GetOpt\Argument;
use GetOpt\ArgumentException\Invalid;
use GetOpt\Describable;
use GetOpt\GetOpt;
use GetOpt\Operand;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    protected function tearDown(): void
    {
        GetOpt::setLang('en'); // reset the language
        parent::tearDown();
    }

    /** @test */
    public function defaultMessageForOption(): void
    {
        $option = Option::create('a', 'alpha', GetOpt::REQUIRED_ARGUMENT)
            ->setValidation('is_numeric');

        self::expectException(Invalid::class);
        self::expectExceptionMessage(sprintf('Option \'%s\' has an invalid value', 'alpha'));

        $option->setValue('foo');
    }

    /** @test */
    public function defaultMessageForOperand(): void
    {
        $operand = Operand::create('alpha')
            ->setValidation('is_numeric');

        self::expectException(Invalid::class);
        self::expectExceptionMessage(sprintf('Operand \'%s\' has an invalid value', 'alpha'));

        $operand->setValue('foo');
    }

    /** @test */
    public function defaultMessageForArgument(): void
    {
        $argument = new Argument(null, 'is_numeric', 'alpha');

        self::expectException(Invalid::class);
        self::expectExceptionMessage(sprintf('Argument \'%s\' has an invalid value', 'alpha'));

        $argument->setValue('foo');
    }

    /** @test */
    public function usesCustomMessage(): void
    {
        $option = Option::create('a', 'alpha', GetOpt::REQUIRED_ARGUMENT)
            ->setValidation('is_numeric', 'alpha has to be numeric');

        self::expectException(Invalid::class);
        self::expectExceptionMessage('Alpha has to be numeric');

        $option->setValue('foo');
    }

    /** @test */
    public function usesTranslatedDescriptions(): void
    {
        GetOpt::setLang('de');
        $operand = Operand::create('alpha')
            ->setValidation('is_numeric', 'Die value von %s muss numerisch sein');

        self::expectException(Invalid::class);
        self::expectExceptionMessage(sprintf('Die value von Operand \'%s\' muss numerisch sein', 'alpha'));

        $operand->setValue('foo');
    }

    /** @test */
    public function providesValueAsSecondReplacement(): void
    {
        $option = Option::create('a', 'alpha', GetOpt::REQUIRED_ARGUMENT)
            ->setValidation('is_numeric', '%s %s');

        self::expectException(Invalid::class);
        self::expectExceptionMessage('Option \'alpha\' foo');

        $option->setValue('foo');
    }

    /** @test */
    public function usesCallbackToGetMessage(): void
    {
        $option = Option::create('a', 'alpha', GetOpt::REQUIRED_ARGUMENT)
            ->setValidation('is_numeric', function () {
                return 'alpha has to be numeric';
            });

        self::expectException(Invalid::class);
        self::expectExceptionMessage('alpha has to be numeric');

        $option->setValue('foo');
    }

    /** @test */
    public function providesOptionAndValue(): void
    {
        $option = Option::create('a', 'alpha', GetOpt::REQUIRED_ARGUMENT);
        $option->setValidation('is_numeric', function (Describable $object, $value) use ($option) {

            self::assertSame('foo', $value);
            self::assertSame($option, $object);

            return 'anything';
        });

        self::expectException(Invalid::class);
        $option->setValue('foo');
    }

    /** @test */
    public function providesOperandAndValue(): void
    {
        $operand = Operand::create('alpha');
        $operand->setValidation('is_numeric', function (Describable $object, $value) use ($operand) {

            self::assertSame('foo', $value);
            self::assertSame($operand, $object);

            return 'anything';
        });

        self::expectException(Invalid::class);
        $operand->setValue('foo');
    }
}
