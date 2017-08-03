<?php

namespace GetOpt;

use PHPUnit\Framework\TestCase;

class GetoptTest extends TestCase
{
    public function testAddOptions()
    {
        $getopt = new GetOpt();
        $getopt->addOptions('a:');
        $getopt->addOptions([
            [ 's', null, GetOpt::OPTIONAL_ARGUMENT ],
            [ null, 'long', GetOpt::OPTIONAL_ARGUMENT ],
            [ 'n', 'name', GetOpt::OPTIONAL_ARGUMENT ]
        ]);

        $getopt->process('-a aparam -s sparam --long longparam');

        $this->assertEquals('aparam', $getopt->getOption('a'));
        $this->assertEquals('longparam', $getopt->getOption('long'));
        $this->assertEquals('sparam', $getopt->getOption('s'));
    }

    public function testAddOptionsChooseShortOrLongAutomatically()
    {
        $getopt = new GetOpt();
        $getopt->addOptions([
            [ 's' ],
            [ 'long', GetOpt::OPTIONAL_ARGUMENT ]
        ]);

        $getopt->process('-s --long longparam');
        $this->assertEquals('longparam', $getopt->getOption('long'));
        $this->assertEquals('1', $getopt->getOption('s'));
    }

    public function testAddOptionsUseDefaultArgumentType()
    {
        $getopt = new GetOpt(null, [
            GetOpt::SETTING_DEFAULT_MODE => GetOpt::REQUIRED_ARGUMENT
        ]);
        $getopt->addOptions([
            [ 'l', 'long' ]
        ]);

        $getopt->process('-l something');
        $this->assertEquals('something', $getopt->getOption('l'));

        $getopt->process('--long someOtherThing');
        $this->assertEquals('someOtherThing', $getopt->getOption('long'));
    }

    public function testAddOptionsFailsOnInvalidArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $getopt = new GetOpt(null);
        $getopt->addOptions(new Option('a', 'alpha'));
    }

    public function testChangeModeAfterwards()
    {
        $getopt = new GetOpt([
            [ 'a', null, GetOpt::REQUIRED_ARGUMENT ]
        ]);

        $getopt->getOption('a', true)->setMode(GetOpt::NO_ARGUMENT);
        $getopt->process('-a foo');

        $this->assertEquals(1, $getopt->getOption('a'));
        $this->assertEquals('foo', $getopt->getOperand(0));
    }

    public function testAddOptionsFailsOnConflict()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $getopt = new GetOpt([
            [ 'v', 'version' ]
        ]);
        $getopt->addOptions([
            [ 'v', 'verbose' ]
        ]);
    }

    public function testParseUsesGlobalArgvWhenNoneGiven()
    {
        $_SERVER['argv'] = [ 'foo.php', '-a' ];

        $getopt = new GetOpt('a');
        $getopt->process();
        $this->assertEquals(1, $getopt->getOption('a'));
    }

    public function testAccessMethods()
    {
        $getopt = new GetOpt('a');
        $getopt->process('-a foo');

        $options = $getopt->getOptions();
        $this->assertCount(1, $options);
        $this->assertEquals(1, $options['a']);
        $this->assertEquals(1, $getopt->getOption('a'));

        $operands = $getopt->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('foo', $operands[0]);
        $this->assertEquals('foo', $getopt->getOperand(0));
    }

    public function testCountable()
    {
        $getopt = new GetOpt([
            new Option('a', 'alpha'),
            new Option('b', 'beta'),
            new Option('c', 'gamma'),
        ]);
        $getopt->process('-abc');
        $this->assertEquals(3, count($getopt));
    }

    public function testArrayAccess()
    {
        $getopt = new GetOpt('q');
        $getopt->process('-q');
        $this->assertEquals(1, $getopt['q']);
    }

    public function testIterable()
    {
        $getopt = new GetOpt([
            [ null, 'alpha', GetOpt::NO_ARGUMENT ],
            [ 'b', 'beta', GetOpt::REQUIRED_ARGUMENT ]
        ]);
        $getopt->process('--alpha -b foo');
        $expected = [ 'alpha' => 1, 'b' => 'foo' ]; // 'beta' should not occur
        foreach ($getopt as $option => $value) {
            $this->assertEquals($expected[$option], $value);
        }
    }

    public function testHelpTextWithCustomScriptName()
    {
        $getopt = new GetOpt();
        $getopt->set(GetOpt::SETTING_SCRIPT_NAME, 'test');
        $expected = "Usage: test [operands]\n";
        $this->assertSame($expected, $getopt->getHelpText());
    }

    public function testThrowsWithInvalidParameter()
    {
        $this->setExpectedException('InvalidArgumentException');
        $getopt = new GetOpt();

        $getopt->process(42);
    }

    public function testAddOptionByString()
    {
        $getopt = new GetOpt();
        $getopt->addOption('c');

        $this->assertEquals(new Option('c', null), $getopt->getOption('c', true));
    }

    public function testThrowsForUnparsableString()
    {
        $this->setExpectedException('InvalidArgumentException');
        $getopt = new GetOpt();

        $getopt->addOption('');
    }

    public function testThrowsForInvalidParameter()
    {
        $this->setExpectedException('InvalidArgumentException');
        $getopt = new GetOpt();

        $getopt->addOption(42);
    }

    public function testIssetArrayAccess()
    {
        $getopt = new GetOpt();
        $getopt->addOption('a');
        $getopt->process('-a');

        $result = isset($getopt['a']);

        self::assertTrue($result);
    }

    public function testRestirctsArraySet()
    {
        $this->setExpectedException('LogicException');
        $getopt = new GetOpt();

        $getopt['a'] = 'test';
    }

    public function testRestirctsArrayUnset()
    {
        $this->setExpectedException('LogicException');
        $getopt = new GetOpt();
        $getopt->addOption('a');
        $getopt->process('-a');

        unset($getopt['a']);
    }

    public function testAddCommandWithConflictingOptions()
    {
        $this->setExpectedException('InvalidArgumentException');

        $getopt = new GetOpt([
            new Option('a'),
        ]);

        $getopt->addCommand(new Command('test', 'Test that it throws', 'var_dump', [
            new Option('a'),
        ]));
    }

    public function testGetCommandByName()
    {
        $cmd1 = new Command('help', 'var_dump');
        $cmd2 = new Command('test', 'var_dump');
        $getopt = new GetOpt();

        $getopt->addCOmmands([ $cmd1, $cmd2 ]);

        self::assertSame($cmd1, $getopt->getCommand('help'));
        self::assertSame($cmd2, $getopt->getCommand('test'));
        self::assertNull($getopt->getCommand());
    }
}
