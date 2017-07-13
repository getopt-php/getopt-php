<?php

namespace GetOpt;

use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    /** @var Command */
    protected $command;
    protected $options = array();

    protected function setUp()
    {
        parent::setUp();

        $this->options = array(
            new Option('a', 'opta'),
            new Option('b', 'optb'),
        );
        $this->command = new Command(
            'the-name',
            'a short description',
            array('\PDO', 'getAvailableDrivers'),
            $this->options,
            'a long description might be longer'
        );
    }

    public function testConstructorSavesName()
    {
        self::assertSame('the-name', $this->command->getName());
    }

    /** @dataProvider dataNamesNotAllowed */
    public function testNamesNotAllowed($name)
    {
        $this->setExpectedException('InvalidArgumentException');
        $command = new Command($name, '', null);
    }

    public function dataNamesNotAllowed()
    {
        return [
            ['-abc'],  // starts with dash
            [''],      // is empty
            ['df ae'], // has spaces
        ];
    }

    public function testConstructorSavesDescription()
    {
        self::assertSame('a short description', $this->command->getDescription(true));
    }

    public function testConstructorSavesLongDescription()
    {
        self::assertSame('a long description might be longer', $this->command->getDescription());
    }

    public function testConstructorSavesHandler()
    {
        self::assertSame(array('\PDO', 'getAvailableDrivers'), $this->command->getHandler());
    }

    public function testConstructorSavesOptions()
    {
        self::assertSame($this->options, $this->command->getOptions());
    }

    public function testAddOptionsAppendsOptions()
    {
        $optionC = new Option('c', 'optc');
        $this->command->addOptions(array($optionC));

        self::assertSame(array($this->options[0], $this->options[1], $optionC), $this->command->getOptions());
    }

    public function testConstructorUsesShortDescription()
    {
        $command = new Command(
            'test',
            'short description',
            'var_dump'
        );

        self::assertSame('short description', $command->getDescription());
    }
}
