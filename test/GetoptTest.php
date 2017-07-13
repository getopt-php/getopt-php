<?php

namespace GetOpt;

use PHPUnit\Framework\TestCase;

class GetoptTest extends TestCase
{
    public function testAddOptions()
    {
        $getopt = new Getopt();
        $getopt->addOptions('a:');
        $getopt->addOptions(
            array(
                array('s', null, Getopt::OPTIONAL_ARGUMENT),
                array(null, 'long', Getopt::OPTIONAL_ARGUMENT),
                array('n', 'name', Getopt::OPTIONAL_ARGUMENT)
            )
        );

        $getopt->parse('-a aparam -s sparam --long longparam');

        $this->assertEquals('aparam', $getopt->getOption('a'));
        $this->assertEquals('longparam', $getopt->getOption('long'));
        $this->assertEquals('sparam', $getopt->getOption('s'));
    }

    public function testAddOptionsChooseShortOrLongAutomatically()
    {
        $getopt = new Getopt();
        $getopt->addOptions(
            array(
                array('s'),
                array('long', Getopt::OPTIONAL_ARGUMENT)
            )
        );

        $getopt->parse('-s --long longparam');
        $this->assertEquals('longparam', $getopt->getOption('long'));
        $this->assertEquals('1', $getopt->getOption('s'));
    }

    public function testAddOptionsUseDefaultArgumentType()
    {
        $getopt = new Getopt(null, array(
            Getopt::SETTING_DEFAULT_MODE => Getopt::REQUIRED_ARGUMENT
        ));
        $getopt->addOptions(
            array(
                array('l', 'long')
            )
        );

        $getopt->parse('-l something');
        $this->assertEquals('something', $getopt->getOption('l'));

        $getopt->parse('--long someOtherThing');
        $this->assertEquals('someOtherThing', $getopt->getOption('long'));
    }

    public function testAddOptionsFailsOnInvalidArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $getopt = new Getopt(null);
        $getopt->addOptions(new Option('a', 'alpha'));
    }

    public function testChangeModeAfterwards()
    {
        $getopt = new Getopt(array(
            array('a', null, Getopt::REQUIRED_ARGUMENT)
        ));

        $getopt->getOption('a', true)->setMode(Getopt::NO_ARGUMENT);
        $getopt->parse('-a foo');

        $this->assertEquals(1, $getopt->getOption('a'));
        $this->assertEquals('foo', $getopt->getOperand(0));
    }

    public function testAddOptionsFailsOnConflict()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $getopt = new Getopt(array(
            array('v', 'version')
        ));
        $getopt->addOptions(array(
            array('v', 'verbose')
        ));
    }

    public function testParseUsesGlobalArgvWhenNoneGiven()
    {
        $_SERVER['argv'] = array('foo.php', '-a');

        $getopt = new Getopt('a');
        $getopt->parse();
        $this->assertEquals(1, $getopt->getOption('a'));
    }

    public function testAccessMethods()
    {
        $getopt = new Getopt('a');
        $getopt->parse('-a foo');

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
        $getopt = new Getopt(array(
            new Option('a', 'alpha'),
            new Option('b', 'beta'),
            new Option('c', 'gamma'),
        ));
        $getopt->parse('-abc');
        $this->assertEquals(3, count($getopt));
    }

    public function testArrayAccess()
    {
        $getopt = new Getopt('q');
        $getopt->parse('-q');
        $this->assertEquals(1, $getopt['q']);
    }

    public function testIterable()
    {
        $getopt = new Getopt(array(
            array(null, 'alpha', Getopt::NO_ARGUMENT),
            array('b', 'beta', Getopt::REQUIRED_ARGUMENT)
        ));
        $getopt->parse('--alpha -b foo');
        $expected = array('alpha' => 1, 'b' => 'foo'); // 'beta' should not occur
        foreach ($getopt as $option => $value) {
            $this->assertEquals($expected[$option], $value);
        }
    }

    public function testHelpText()
    {
        $getopt = new Getopt(array(
            array('a', 'alpha', Getopt::NO_ARGUMENT, 'Short and long options with no argument'),
            array(null, 'beta', Getopt::OPTIONAL_ARGUMENT, 'Long option only with an optional argument'),
            array('c', null, Getopt::REQUIRED_ARGUMENT, 'Short option only with a mandatory argument')
        ));
        $getopt->parse('');

        $script = $_SERVER['PHP_SELF'];

        $expected = "Usage: $script [options] [operands]\n";
        $expected .= "Options:\n";
        $expected .= "  -a, --alpha     Short and long options with no argument\n";
        $expected .= "  --beta [<arg>]  Long option only with an optional argument\n";
        $expected .= "  -c <arg>        Short option only with a mandatory argument\n";

        $this->assertEquals($expected, $getopt->getHelpText());
    }

    public function testHelpTextWithoutDescriptions()
    {
        $getopt = new Getopt(array(
            array('a', 'alpha', Getopt::NO_ARGUMENT),
            array(null, 'beta', Getopt::OPTIONAL_ARGUMENT),
            array('c', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $getopt->parse('');

        $script = $_SERVER['PHP_SELF'];

        $expected = "Usage: $script [options] [operands]\n";
        $expected .= "Options:\n";
        $expected .= "  -a, --alpha     \n";
        $expected .= "  --beta [<arg>]  \n";
        $expected .= "  -c <arg>        \n";

        $this->assertEquals($expected, $getopt->getHelpText());
    }

    public function testHelpTextWithLongDescriptions()
    {
        defined('COLUMNS') || define('COLUMNS', 90);
        $getopt = new Getopt(array(
            array('a', 'alpha', Getopt::NO_ARGUMENT, 'Short and long options with no argument and a very long text ' .
                                                     'that exceeds the length of the row'),
            array(null, 'beta', Getopt::OPTIONAL_ARGUMENT, 'Long option only with an optional argument'),
            array('c', null, Getopt::REQUIRED_ARGUMENT, 'Short option only with a mandatory argument')
        ));
        $getopt->parse('');

        $script = $_SERVER['PHP_SELF'];

        $expected = "Usage: $script [options] [operands]\n" .
                    "Options:\n" .
                    "  -a, --alpha     Short and long options with no argument and a very long text that\n" .
                    "                  exceeds the length of the row\n" .
                    "  --beta [<arg>]  Long option only with an optional argument\n" .
                    "  -c <arg>        Short option only with a mandatory argument\n";

        $this->assertEquals($expected, $getopt->getHelpText());
    }

    public function testHelpTextWithCustomScriptName()
    {
        $getopt = new Getopt();
        $getopt->setScriptName('test');
        $expected = "Usage: test [options] [operands]\n";
        $this->assertSame($expected, $getopt->getHelpText());
    }

    public function testThrowsWithInvalidParameter()
    {
        $this->setExpectedException('InvalidArgumentException');
        $getopt = new Getopt();

        $getopt->process(42);
    }

    public function testAddOptionByString()
    {
        $getopt = new Getopt();
        $getopt->addOption('c');

        $this->assertEquals(new Option('c', null), $getopt->getOption('c', true));
    }

    public function testThrowsForUnparsableString()
    {
        $this->setExpectedException('InvalidArgumentException');
        $getopt = new Getopt();

        $getopt->addOption('');
    }

    public function testThrowsForInvalidParameter()
    {
        $this->setExpectedException('InvalidArgumentException');
        $getopt = new Getopt();

        $getopt->addOption(42);
    }

    public function testIssetArrayAccess()
    {
        $getopt = new Getopt();
        $getopt->addOption('a');
        $getopt->process('-a');

        $result = isset($getopt['a']);

        self::assertTrue($result);
    }

    public function testRestirctsArraySet()
    {
        $this->setExpectedException('LogicException');
        $getopt = new Getopt();

        $getopt['a'] = 'test';
    }

    public function testRestirctsArrayUnset()
    {
        $this->setExpectedException('LogicException');
        $getopt = new Getopt();
        $getopt->addOption('a');
        $getopt->process('-a');

        unset($getopt['a']);
    }

    public function testAddCommandWithConflictingOptions()
    {
        $this->setExpectedException('InvalidArgumentException');

        $getopt = new Getopt(array(
            new Option('a'),
        ));

        $getopt->addCommand(new Command('test', 'Test that it throws', 'var_dump', array(
            new Option('a'),
        )));
    }

    public function testGetCommandByName()
    {
        $cmd1 = new Command('help', 'Get help for command', 'var_dump');
        $cmd2 = new Command('test', 'Test commands', 'var_dump');
        $getopt = new Getopt();

        $getopt->addCOmmands(array( $cmd1, $cmd2 ));

        self::assertSame($cmd1, $getopt->getCommand('help'));
        self::assertSame($cmd2, $getopt->getCommand('test'));
        self::assertNull($getopt->getCommand());
    }
}
