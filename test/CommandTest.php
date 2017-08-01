<?php

namespace GetOpt;

use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    /** @var Command */
    protected $command;
    protected $options = [];

    protected function setUp()
    {
        parent::setUp();

        $this->options = [
            new Option('a', 'opta'),
            new Option('b', 'optb'),
        ];
        $this->command = new Command(
            'the-name',
            'a short description',
            [ '\PDO', 'getAvailableDrivers' ],
            $this->options,
            'a long description might be longer'
        );
    }

    public function testConstructorSavesName()
    {
        self::assertSame('the-name', $this->command->name());
    }

    /** @dataProvider dataNamesNotAllowed
     * @param string $name
     */
    public function testNamesNotAllowed($name)
    {
        $this->setExpectedException('InvalidArgumentException');
        new Command($name, '', null);
    }

    public function dataNamesNotAllowed()
    {
        return [
            [ '-abc' ],  // starts with dash
            [ '' ],      // is empty
            [ 'df ae' ], // has spaces
        ];
    }

    public function testConstructorSavesDescription()
    {
        self::assertSame('a short description', $this->command->description(true));
    }

    public function testConstructorSavesLongDescription()
    {
        self::assertSame('a long description might be longer', $this->command->description());
    }

    public function testConstructorSavesHandler()
    {
        self::assertSame([ '\PDO', 'getAvailableDrivers' ], $this->command->handler());
    }

    public function testConstructorSavesOptions()
    {
        self::assertSame($this->options, $this->command->getOptions());
    }

    public function testAddOptionsAppendsOptions()
    {
        $optionC = new Option('c', 'optc');
        $this->command->addOptions([ $optionC ]);

        self::assertSame([ $this->options[0], $this->options[1], $optionC ], $this->command->getOptions());
    }

    public function testConstructorUsesShortDescription()
    {
        $command = new Command(
            'test',
            'short description',
            'var_dump'
        );

        self::assertSame('short description', $command->description());
    }

    public function testGetHelpForExecutedCommand()
    {
        $longDescription = 'This is a very long description.' . PHP_EOL . 'It also may have line breaks.';
        $getopt = new GetOpt();
        $getopt->addCommand(new Command(
            'test',
            '',
            'var_dump',
            [ Option::create('a', 'alpha')->setDescription('enable alpha') ],
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
        $getopt = new GetOpt([
            Option::create('h', 'help')->setDescription('Shows this help')
        ]);
        $getopt->addCommands([ $cmd1, $cmd2 ]);
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

    public function testTooLongShortDescription()
    {
        defined('COLUMNS') || define('COLUMNS', 90);
        $getopt = new GetOpt([
            Option::create('h', 'help')->setDescription('Shows this help')
        ]);
        $getopt->addCommands([new Command(
            'help',
            'This is a too long help text to have it on one row. It is also too long for a short description. ' .
            'You should avoid such long texts for a short description.',
            'var_dump'
        )]);
        $script = $_SERVER['PHP_SELF'];

        $help = $getopt->getHelpText();

        self::assertSame(
            'Usage: ' . $script . ' [command] [options] [operands]' . PHP_EOL .
            'Options:' . PHP_EOL .
            '  -h, --help  Shows this help' . PHP_EOL .
            'Commands:' . PHP_EOL .
            '  help  This is a too long help text to have it on one row. It is also too long for a' . PHP_EOL .
            '        short description. You should avoid such long texts for a short description.' . PHP_EOL,
            $help
        );
    }
}
