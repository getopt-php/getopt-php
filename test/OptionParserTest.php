<?php

namespace GetOpt\Test;

use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\OptionParser;
use PHPUnit\Framework\TestCase;

class OptionParserTest extends TestCase
{
    /** @var OptionParser */
    private $parser;

    public function setUp()
    {
        $this->parser = new OptionParser(GetOpt::REQUIRED_ARGUMENT);
    }

    /** @test */
    public function parseString()
    {
        $options = $this->parser->parseString('ab:c::3');
        self::assertInternalType('array', $options);
        self::assertCount(4, $options);
        foreach ($options as $option) {
            self::assertInstanceOf(Option::CLASSNAME, $option);
            self::assertNull($option->getLong());
            switch ($option->getShort()) {
                case 'a':
                case '3':
                    self::assertSame(GetOpt::NO_ARGUMENT, $option->getMode());
                    break;
                case 'b':
                    self::assertSame(GetOpt::REQUIRED_ARGUMENT, $option->getMode());
                    break;
                case 'c':
                    self::assertSame(GetOpt::OPTIONAL_ARGUMENT, $option->getMode());
                    break;
                default:
                    $this->fail('Unexpected option: '.$option->getShort());
            }
        }
    }

    /** @test */
    public function parseStringEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->parser->parseString('');
    }

    /** @test */
    public function parseStringInvalidCharacter()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->parser->parseString('ab:c::dä');
    }

    /** @test */
    public function parseStringStartsWithColon()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->parser->parseString(':ab:c::d');
    }

    /** @test */
    public function parseStringTripleColon()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->parser->parseString('ab:c:::d');
    }

    public function provideOptionArrays()
    {
        return [
            [ [ 'a', 'alpha', GetOpt::OPTIONAL_ARGUMENT, 'Description', 42 ] ],
            [ [ 'b', 'beta' ] ],
            [ [ 'c' ] ],
        ];
    }

    /** @dataProvider provideOptionArrays
     * @param array $array
     * @test */
    public function parseArray($array)
    {
        $option = $this->parser->parseArray($array);

        self::assertInstanceOf(Option::CLASSNAME, $option);
        switch ($option->getShort()) {
            case 'a':
                self::assertSame('alpha', $option->getLong());
                self::assertSame(GetOpt::OPTIONAL_ARGUMENT, $option->getMode());
                self::assertSame('Description', $option->getDescription());
                self::assertSame(42, $option->getArgument()->getDefaultValue());
                break;
            case 'b':
                self::assertSame('beta', $option->getLong());
                self::assertSame(GetOpt::REQUIRED_ARGUMENT, $option->getMode());
                self::assertSame('', $option->getDescription());
                break;
            case 'c':
                self::assertNull($option->getLong());
                self::assertSame(GetOpt::REQUIRED_ARGUMENT, $option->getMode());
                self::assertSame('', $option->getDescription());
                self::assertFalse($option->getArgument()->hasDefaultValue());
                break;
            default:
                $this->fail('Unexpected option: '.$option->getShort());
        }
    }

    /** @test */
    public function parseArrayEmpty()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->parser->parseArray([]);
    }

    /** @test */
    public function parseArrayInvalid()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->parser->parseArray([ 'a', '_' ]);
    }
}
