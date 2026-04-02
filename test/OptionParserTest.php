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

    public function setUp(): void
    {
        $this->parser = new OptionParser();
        // reset default mode
        $this->parser::$defaultMode = GetOpt::NO_ARGUMENT;
    }

    /** @test */
    public function parseString(): void
    {
        $options = $this->parser->parseString('ab:c::3');
        self::assertIsArray($options);
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
    public function parseStringEmpty(): void
    {
        self::expectException(\InvalidArgumentException::class);
        $this->parser->parseString('');
    }

    /** @test */
    public function parseStringInvalidCharacter(): void
    {
        self::expectException(\InvalidArgumentException::class);
        $this->parser->parseString('ab:c::dä');
    }

    /** @test */
    public function parseStringStartsWithColon(): void
    {
        self::expectException(\InvalidArgumentException::class);
        $this->parser->parseString(':ab:c::d');
    }

    /** @test */
    public function parseStringTripleColon(): void
    {
        self::expectException(\InvalidArgumentException::class);
        $this->parser->parseString('ab:c:::d');
    }

    public function provideOptionArrays()
    {
        return [
            [
                [ 'a', 'alpha', GetOpt::OPTIONAL_ARGUMENT, 'Description', 42 ],
                Option::create('a', 'alpha', GetOpt::OPTIONAL_ARGUMENT)
                    ->setDescription('Description')
                    ->setDefaultValue(42),
            ],
            [
                [ 'b', 'beta'],
                Option::create('b', 'beta'),
            ],
            [
                [ 'c' ],
                Option::create('c'),
            ],
            [
                [ 'delta', GetOpt::REQUIRED_ARGUMENT ],
                Option::create(null, 'delta', GetOpt::REQUIRED_ARGUMENT),
            ],
            [
                [ 'e', 'epsilon', GetOpt::OPTIONAL_ARGUMENT, null],
                Option::create('e', 'epsilon', GetOpt::OPTIONAL_ARGUMENT),
            ]
        ];
    }

    /** @dataProvider provideOptionArrays
     * @param array $array
     * @test */
    public function parseArray($array, $expected): void
    {
        $option = $this->parser->parseArray($array);

        self::assertEquals($expected, $option);
    }

    /** @test */
    public function parseArrayEmpty(): void
    {
        self::expectException(\InvalidArgumentException::class);
        $this->parser->parseArray([]);
    }

    /** @test */
    public function parseArrayInvalid(): void
    {
        self::expectException(\InvalidArgumentException::class);
        $this->parser->parseArray([ 'a', '_' ]);
    }
}
