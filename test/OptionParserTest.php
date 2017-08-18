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
        $this->assertInternalType('array', $options);
        $this->assertCount(4, $options);
        foreach ($options as $option) {
            $this->assertInstanceOf(Option::CLASSNAME, $option);
            $this->assertNull($option->long());
            switch ($option->short()) {
                case 'a':
                case '3':
                    $this->assertEquals(GetOpt::NO_ARGUMENT, $option->mode());
                    break;
                case 'b':
                    $this->assertEquals(GetOpt::REQUIRED_ARGUMENT, $option->mode());
                    break;
                case 'c':
                    $this->assertEquals(GetOpt::OPTIONAL_ARGUMENT, $option->mode());
                    break;
                default:
                    $this->fail('Unexpected option: '.$option->short());
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

        $this->assertInstanceOf(Option::CLASSNAME, $option);
        switch ($option->short()) {
            case 'a':
                $this->assertEquals('alpha', $option->long());
                $this->assertEquals(GetOpt::OPTIONAL_ARGUMENT, $option->mode());
                $this->assertEquals('Description', $option->description());
                $this->assertEquals(42, $option->getArgument()->getDefaultValue());
                break;
            case 'b':
                $this->assertEquals('beta', $option->long());
                $this->assertEquals(GetOpt::REQUIRED_ARGUMENT, $option->mode());
                $this->assertEquals('', $option->description());
                break;
            case 'c':
                $this->assertNull($option->long());
                $this->assertEquals(GetOpt::REQUIRED_ARGUMENT, $option->mode());
                $this->assertEquals('', $option->description());
                $this->assertFalse($option->getArgument()->hasDefaultValue());
                break;
            default:
                $this->fail('Unexpected option: '.$option->short());
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
        $this->parser->parseArray([ 'a', 'b' ]);
    }
}
