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
        return array(
            array('-abc'),  // starts with dash
            array(''),      // is empty
            array('df ae'), // has spaces
        );
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

    public function testGetHelpForExecutedCommand()
    {
        $longDescription = 'This is a very long description.' . PHP_EOL . 'It also may have line breaks.';
        $getopt = new Getopt();
        $getopt->addCommand(new Command(
            'test',
            '',
            'var_dump',
            array(Option::create('a', 'alpha')->setDescription('enable alpha')),
            $longDescription
        ));
        $script = $_SERVER['PHP_SELF'];

        $getopt->process('test');
        $help = $getopt->getHelpText();

        self::assertSame(
            'Usage: ' . $script . ' test [options] [operands]' . PHP_EOL .
            '' . PHP_EOL .
            $longDescription . PHP_EOL . PHP_EOL .
            'Options:' . PHP_EOL .
            '  -a, --alpha  enable alpha' . PHP_EOL,
            $help
        );
    }

    public function testGetHelpForCommands()
    {
        $cmd1 = new Command('help', 'Shows help for a command', 'var_dump');
        $cmd2 = new Command('run:tests', 'Executes the tests', 'var_dump');
        $getopt = new Getopt(array(
            Option::create('h', 'help')->setDescription('Shows this help')
        ));
        $getopt->addCommands(array($cmd1, $cmd2));
        $script = $_SERVER['PHP_SELF'];

        $help = $getopt->getHelpText();

        self::assertSame(
            'Usage: ' . $script . ' [command] [options] [operands]' . PHP_EOL .
            'Options:' . PHP_EOL .
            '  -h, --help  Shows this help' . PHP_EOL .
            'Commands:' . PHP_EOL .
            '  help       Shows help for a command' . PHP_EOL .
            '  run:tests  Executes the tests' . PHP_EOL,
            $help
        );
    }
}
