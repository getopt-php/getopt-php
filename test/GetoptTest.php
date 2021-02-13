<?php

namespace GetOpt\Test;

use GetOpt\ArgumentException\Missing;
use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Help;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class GetoptTest extends TestCase
{
    protected function tearDown()
    {
        GetOpt::setLang('en');
        parent::tearDown();
    }

    /** @test */
    public function addOptions()
    {
        $getopt = new GetOpt();
        $getopt->addOptions('a:');
        $getopt->addOptions([
            [ 's', null, GetOpt::OPTIONAL_ARGUMENT ],
            [ null, 'long', GetOpt::OPTIONAL_ARGUMENT ],
            [ 'n', 'name', GetOpt::OPTIONAL_ARGUMENT ]
        ]);

        $getopt->process('-a aparam -s sparam --long longparam');

        self::assertSame('aparam', $getopt->getOption('a'));
        self::assertSame('longparam', $getopt->getOption('long'));
        self::assertSame('sparam', $getopt->getOption('s'));
    }

    /** @test */
    public function addOptionsChooseShortOrLongAutomatically()
    {
        $getopt = new GetOpt();
        $getopt->addOptions([
            [ 's' ],
            [ 'long', GetOpt::OPTIONAL_ARGUMENT ]
        ]);

        $getopt->process('-s --long longparam');
        self::assertSame('longparam', $getopt->getOption('long'));
        self::assertSame(1, $getopt->getOption('s'));
    }

    /** @test */
    public function addOptionsUseDefaultArgumentType()
    {
        $getopt = new GetOpt(null, [
            GetOpt::SETTING_DEFAULT_MODE => GetOpt::REQUIRED_ARGUMENT
        ]);
        $getopt->addOptions([
            [ 'l', 'long' ]
        ]);

        self::expectException(Missing::class);

        $getopt->process('--long');
    }

    /** @test */
    public function addOptionsFailsOnInvalidArgument()
    {
        self::expectException(\InvalidArgumentException::class);
        $getopt = new GetOpt(null);
        $getopt->addOptions(new Option('a', 'alpha'));
    }

    /** @test */
    public function changeModeAfterwards()
    {
        $getopt = new GetOpt([
            [ 'a', null, GetOpt::REQUIRED_ARGUMENT ]
        ]);

        $getopt->getOption('a', true)->setMode(GetOpt::NO_ARGUMENT);
        $getopt->process('-a foo');

        self::assertSame(1, $getopt->getOption('a'));
        self::assertSame('foo', $getopt->getOperand(0));
    }


    /** @return array */
    public function provideConflictOptions()
    {
        return [
            [[
                new Option('v', 'version'),
                new Option('v', 'verbose'),
            ]],
            [[
                new Option('v', 'version'),
                new Option(null, 'v'),
            ]],
        ];
    }

    /**
     * @dataProvider provideConflictOptions
     * @test
     * @param array $options
     */
    public function addOptionsFailsOnConflict($options)
    {
        self::expectException(\InvalidArgumentException::class);
        $getopt = new GetOpt();
        $getopt->addOptions($options);
    }

    /** @test */
    public function parseUsesGlobalArgvWhenNoneGiven()
    {
        $_SERVER['argv'] = [ 'foo.php', '-a' ];

        $getopt = new GetOpt('a');
        $getopt->process();
        self::assertSame(1, $getopt->getOption('a'));
    }

    /** @test */
    public function accessMethods()
    {
        $getopt = new GetOpt('a');
        $getopt->process('-a foo');

        $options = $getopt->getOptions();
        self::assertCount(1, $options);
        self::assertSame(1, $options['a']);
        self::assertSame(1, $getopt->getOption('a'));

        $operands = $getopt->getOperands();
        self::assertCount(1, $operands);
        self::assertSame('foo', $operands[0]);
        self::assertSame('foo', $getopt->getOperand(0));
    }

    /** @test */
    public function countable()
    {
        $getopt = new GetOpt([
            new Option('a', 'alpha'),
            new Option('b', 'beta'),
            new Option('c', 'gamma'),
        ]);
        $getopt->process('-abc');
        self::assertSame(3, count($getopt));
    }

    /** @test */
    public function arrayAccess()
    {
        $getopt = new GetOpt('q');
        $getopt->process('-q');
        self::assertSame(1, $getopt['q']);
    }

    /** @test */
    public function iterable()
    {
        $getopt = new GetOpt([
            [ null, 'alpha', GetOpt::NO_ARGUMENT ],
            [ 'b', 'beta', GetOpt::REQUIRED_ARGUMENT ]
        ]);
        $getopt->process('--alpha -b foo');

        $result = iterator_to_array($getopt->getIterator());
        $expected = iterator_to_array(new \ArrayIterator([ 'alpha' => 1, 'beta' => 'foo' ]));

        self::assertSame($expected, $result);
    }

    /** @test */
    public function iteratesOverEmptyStrings()
    {
        $getopt = new GetOpt([
            [ 'a', 'alpha' , GetOpt::REQUIRED_ARGUMENT ]
        ]);
        $getopt->process('--alpha ""');

        $result = iterator_to_array($getopt->getIterator());
        $expected = iterator_to_array(new \ArrayIterator([ 'alpha' => '']));

        self::assertSame($expected, $result);
    }

    /** @test */
    public function helpTextWithCustomScriptName()
    {
        $getopt = new GetOpt();
        $getopt->set(GetOpt::SETTING_SCRIPT_NAME, 'test');

        $helpText = $getopt->getHelpText();

        self::assertSame('Usage: test [operands]' . PHP_EOL . PHP_EOL, $helpText);
    }

    /** @test */
    public function helpTextWithDescription()
    {
        $getopt = new GetOpt();
        $getopt->set(GetOpt::SETTING_SCRIPT_NAME, 'test');

        $helpText = $getopt->getHelpText([
            Help::DESCRIPTION => 'Running the tests',
        ]);

        self::assertSame(
            'Usage: test [operands]' . PHP_EOL . PHP_EOL .
            'Running the tests' . PHP_EOL . PHP_EOL,
            $helpText
        );
    }

    /** @test */
    public function throwsWithInvalidParameter()
    {
        self::expectException(\InvalidArgumentException::class);
        $getopt = new GetOpt();

        $getopt->process(42);
    }

    /** @test */
    public function addOptionByString()
    {
        $getopt = new GetOpt();
        $getopt->addOption('c');

        self::assertSame((string)new Option('c', null), (string)$getopt->getOption('c', true));
    }

    /** @test */
    public function throwsForUnparsableString()
    {
        self::expectException(\InvalidArgumentException::class);
        $getopt = new GetOpt();

        $getopt->addOption('');
    }

    /** @test */
    public function throwsForInvalidParameter()
    {
        self::expectException(\InvalidArgumentException::class);
        $getopt = new GetOpt();

        $getopt->addOption(42);
    }

    /** @test */
    public function issetArrayAccess()
    {
        $getopt = new GetOpt();
        $getopt->addOption('a');
        $getopt->process('-a');

        $result = isset($getopt['a']);

        self::assertTrue($result);
    }

    /** @test */
    public function restirctsArraySet()
    {
        self::expectException(\LogicException::class);
        $getopt = new GetOpt();

        $getopt['a'] = 'test';
    }

    /** @test */
    public function restrictsArrayUnset()
    {
        self::expectException(\LogicException::class);
        $getopt = new GetOpt();
        $getopt->addOption('a');
        $getopt->process('-a');

        unset($getopt['a']);
    }

    /** @test */
    public function addCommandWithConflictingOptions()
    {
        self::expectException(\InvalidArgumentException::class);

        $getopt = new GetOpt([
            new Option('a'),
        ]);

        $getopt->addCommand(new Command('test', 'Test that it throws', 'var_dump', [
            new Option('a'),
        ]));
    }

    /** @test */
    public function getCommandByName()
    {
        $cmd1 = new Command('help', 'var_dump');
        $cmd2 = new Command('test', 'var_dump');
        $getopt = new GetOpt();

        $getopt->addCOmmands([ $cmd1, $cmd2 ]);

        self::assertSame($cmd1, $getopt->getCommand('help'));
        self::assertSame($cmd2, $getopt->getCommand('test'));
        self::assertNull($getopt->getCommand());
    }

    /** @test */
    public function setHelpLangToDe()
    {
        $getopt = new GetOpt();
        $getopt->set(GetOpt::SETTING_SCRIPT_NAME, 'test');
        $getopt->addOption(Option::create('v', 'verbose')->setDescription('Ausführliche Ausgaben aktivieren'));

        $result = $getopt->setLang('de');

        self::assertTrue($result);
        self::assertSame(
            'Verwendung: test [Optionen] [Operanden]' . PHP_EOL . PHP_EOL .
            'Optionen:' . PHP_EOL .
            '  -v, --verbose  Ausführliche Ausgaben aktivieren' . PHP_EOL . PHP_EOL,
            $getopt->getHelpText()
        );
    }

    /** @test */
    public function returnsFalseWhenFileDoesNotExist()
    {
        $getopt = new GetOpt();

        $result = $getopt->setLang('any/path/to/file.php');

        self::assertFalse($result);
    }
}
