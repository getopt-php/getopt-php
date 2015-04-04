<?php

namespace Ulrichsg\Getopt;

class GetoptTest extends \PHPUnit_Framework_TestCase
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

        $result = $getopt->parse('-a aparam -s sparam --long longparam');
        $this->assertEquals('aparam', $result->getOption('a'));
        $this->assertEquals('longparam', $result->getOption('long'));
        $this->assertEquals('sparam', $result->getOption('s'));
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

        $result = $getopt->parse('-s --long longparam');
        $this->assertEquals('longparam', $result->getOption('long'));
        $this->assertEquals('1', $result->getOption('s'));
    }

    public function testAddOptionsUseDefaultArgumentType()
    {
        $getopt = new Getopt(null, Getopt::REQUIRED_ARGUMENT);
        $getopt->addOptions(
            array(
                array('l', 'long')
            )
        );

        $result = $getopt->parse('-l something');
        $this->assertEquals('something', $result->getOption('l'));

        $result = $getopt->parse('--long someOtherThing');
        $this->assertEquals('someOtherThing', $result->getOption('long'));
    }

    public function testAddOptionsFailsOnInvalidArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $getopt = new Getopt(null);
        $getopt->addOptions(new Option('a', 'alpha'));
    }

    public function testAddOptionsOverwritesExistingOptions()
    {
        $getopt = new Getopt(array(
            array('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $getopt->addOptions(array(
            array('a', null, Getopt::NO_ARGUMENT)
        ));
        $result = $getopt->parse('-a foo');

        $this->assertEquals(1, $result->getOption('a'));
        $this->assertEquals('foo', $result->getOperand(0));
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
        global $argv;
        $argv = array('foo.php', '-a');

        $getopt = new Getopt('a');
        $result = $getopt->parse();
        $this->assertEquals(1, $result->getOption('a'));
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
        $expected .= "  -a, --alpha             Short and long options with no argument\n";
        $expected .= "  --beta [<arg>]          Long option only with an optional argument\n";
        $expected .= "  -c <arg>                Short option only with a mandatory argument\n";

        $this->assertEquals($expected, $getopt->getHelpText());
    }
}
