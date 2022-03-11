<?php

namespace GetOpt\Test;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    /** @var Command */
    protected $command;
    protected $options = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->options = [
            new Option('a', 'opta'),
            new Option('b', 'optb'),
        ];
        $this->command = new Command(
            'the-name',
            [ '\PDO', 'getAvailableDrivers' ],
            $this->options
        );
    }

    /** @test */
    public function constructorSavesName(): void
    {
        self::assertSame('the-name', $this->command->getName());
    }

    /** @dataProvider dataNamesNotAllowed
     * @param string $name
     * @test */
    public function namesNotAllowed($name): void
    {
        self::expectException(\InvalidArgumentException::class);
        new Command($name, '', null);
    }

    public function dataNamesNotAllowed()
    {
        return [
            [ '-abc' ],  // starts with dash
            [ 'some -abc' ],  // second word starts with dash
            [ '' ],      // is empty
        ];
    }

    /** @test */
    public function constructorSavesHandler(): void
    {
        self::assertSame([ '\PDO', 'getAvailableDrivers' ], $this->command->getHandler());
    }

    /** @test */
    public function constructorSavesOptions(): void
    {
        self::assertSame($this->options, $this->command->getOptions());
    }

    /** @test */
    public function addOptionsAppendsOptions(): void
    {
        $optionC = new Option('c', 'optc');
        $this->command->addOptions([ $optionC ]);

        self::assertSame([ $this->options[0], $this->options[1], $optionC ], $this->command->getOptions());
    }

    /** @test */
    public function commandWithConflictingOptionsFailsToAdd(): void
    {
        $getOpt = new GetOpt([Option::create('v', 'verbose')]);
        $command = new Command('foo', 'var_dump');
        $command->addOption(Option::create('v', 'var'));

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('$command has conflicting options');

        $getOpt->addCommand($command);
    }

    /** @test */
    public function operandsHaveToFollowCommands(): void
    {
        $getOpt = new GetOpt([Option::create(null, 'version')]);
        $command = new Command('bar', 'var_dump');
        $getOpt->addCommand($command);

        $getOpt->process('foo --version bar');

        self::assertNull($getOpt->getCommand());
        self::assertSame(['foo', 'bar'], $getOpt->getOperands());
    }

    /** @test */
    public function shortDescriptionUsedForDescription(): void
    {
        $command = new Command('test', 'var_dump');

        $command->setShortDescription('short description');

        self::assertSame('short description', $command->getDescription());
    }

    /** @test */
    public function descriptionUsedForShortDescription(): void
    {
        $command = new Command('test', 'var_dump');

        $command->setDescription('long description');

        self::assertSame('long description', $command->getShortDescription());
    }

    /** @test */
    public function getHelpForExecutedCommand(): void
    {
        $longDescription = 'This is a very long description.' . PHP_EOL . 'It also may have line breaks.';
        $getopt = new GetOpt();
        $getopt->addCommand(
            Command::create(
                'test',
                'var_dump',
                [ Option::create('a', 'alpha')->setDescription('enable alpha') ]
            )->setDescription($longDescription)
        );
        $script = $_SERVER['PHP_SELF'];

        $getopt->process('test');
        $help = $getopt->getHelpText();

        self::assertSame(
            'Usage: ' . $script . ' test [options] [operands]' . PHP_EOL . PHP_EOL .
            $longDescription . PHP_EOL . PHP_EOL .
            'Options:' . PHP_EOL .
            '  -a, --alpha  enable alpha' . PHP_EOL . PHP_EOL,
            $help
        );
    }

    /** @test */
    public function getHelpForCommands(): void
    {
        $cmd1 = Command::create('help', 'var_dump')->setDescription('Shows help for a command');
        $cmd2 = Command::create('run:tests', 'var_dump')->setDescription('Executes the tests');
        $getopt = new GetOpt([
            Option::create('h', 'help')->setDescription('Shows this help')
        ]);
        $getopt->addCommands([ $cmd1, $cmd2 ]);
        $script = $_SERVER['PHP_SELF'];

        $help = $getopt->getHelpText();

        self::assertSame(
            'Usage: ' . $script . ' <command> [options] [operands]' . PHP_EOL . PHP_EOL .
            'Options:' . PHP_EOL .
            '  -h, --help  Shows this help' . PHP_EOL . PHP_EOL .
            'Commands:' . PHP_EOL .
            '  help       Shows help for a command' . PHP_EOL .
            '  run:tests  Executes the tests' . PHP_EOL . PHP_EOL,
            $help
        );
    }

    /** @test */
    public function tooLongShortDescription(): void
    {
        defined('COLUMNS') || define('COLUMNS', 90);
        $getopt = new GetOpt([
            Option::create('h', 'help')->setDescription('Shows this help')
        ]);
        $getopt->addCommands([
            Command::create('help', 'var_dump')
                ->setShortDescription(
                    'This is a too long help text to have it on one row. It is also too long for a short ' .
                    'description. You should avoid such long texts for a short description.'
                )
        ]);
        $script = $_SERVER['PHP_SELF'];

        $help = $getopt->getHelpText();

        self::assertSame(
            'Usage: ' . $script . ' <command> [options] [operands]' . PHP_EOL . PHP_EOL .
            'Options:' . PHP_EOL .
            '  -h, --help  Shows this help' . PHP_EOL . PHP_EOL .
            'Commands:' . PHP_EOL .
            '  help  This is a too long help text to have it on one row. It is also too long for a' . PHP_EOL .
            '        short description. You should avoid such long texts for a short description.' . PHP_EOL . PHP_EOL,
            $help
        );
    }

    /** @test */
    public function commandsWithSpaces(): void
    {
        $getOpt = new GetOpt();
        $command = Command::create('import reviews', 'var_dump');
        $getOpt->addCommand($command);

        $getOpt->process('import reviews');

        self::assertSame($command, $getOpt->getCommand());
    }

    /** @test */
    public function singleWordCommandHavePrecedence(): void
    {
        $getOpt = new GetOpt();
        $import = Command::create('import', 'var_dump');
        $importReviews = Command::create('import reviews', 'var_dump');
        $getOpt->addCommands([$import, $importReviews]);

        $getOpt->process('import reviews');

        self::assertSame($import, $getOpt->getCommand());
        self::assertSame(['reviews'], $getOpt->getOperands());
    }

    /** @test */
    public function commandCannotBeDividedByOptions(): void
    {
        $getOpt = new GetOpt([Option::create(null, 'version')]);
        $command = Command::create('import reviews', 'var_dump');
        $getOpt->addCommand($command);

        $getOpt->process('import --version reviews');

        self::assertNull($getOpt->getCommand());
        self::assertSame(['import', 'reviews'], $getOpt->getOperands());
    }
}
