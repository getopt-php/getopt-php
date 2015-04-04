<?php

namespace Ulrichsg\Getopt;

class DefaultHelpTextFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testHelpText()
    {
        $option1 = new Option('a', 'alpha', Getopt::NO_ARGUMENT);
        $option1->setDescription('Short and long options with no argument');

        $option2 = new Option(null, 'beta', Getopt::OPTIONAL_ARGUMENT);
        $option2->setDescription('Long option only with an optional argument');

        $option3 = new Option('c', null, Getopt::REQUIRED_ARGUMENT);
        $option3->setDescription('Short option only with a mandatory argument');

        $options = array($option1, $option2, $option3);

        $expected = "Usage: test [options] [operands]\n";
        $expected .= "Options:\n";
        $expected .= "  -a, --alpha        Short and long options with no argument\n";
        $expected .= "  --beta [<arg>]     Long option only with an optional argument\n";
        $expected .= "  -c <arg>           Short option only with a mandatory argument\n";

        $formatter = new DefaultHelpTextFormatter();
        $formatter->setScriptName('test');
        $this->assertEquals($expected, $formatter->getHelpText($options, 20));
    }

    public function testHelpTextWithoutDescriptions()
    {
        $options = array(
            new Option('a', 'alpha', Getopt::NO_ARGUMENT),
            new Option(null, 'beta', Getopt::OPTIONAL_ARGUMENT),
            new Option('c', null, Getopt::REQUIRED_ARGUMENT)
        );

        $expected = "Usage: test [options] [operands]\n";
        $expected .= "Options:\n";
        $expected .= "  -a, --alpha             \n";
        $expected .= "  --beta [<arg>]          \n";
        $expected .= "  -c <arg>                \n";

        $formatter = new DefaultHelpTextFormatter();
        $formatter->setScriptName('test');
        $this->assertEquals($expected, $formatter->getHelpText($options));
    }

    public function testHelpTextNoOptions()
    {
        $formatter = new DefaultHelpTextFormatter();
        $expected = "Usage:  [options] [operands]\nOptions:\n";
        $this->assertSame($expected, $formatter->getHelpText(array()));
    }

    public function testHelpTextWithCustomBanner()
    {
        $formatter = new DefaultHelpTextFormatter();
        $formatter->setBanner("My custom Banner %s\n");
        $this->assertSame("My custom Banner \nOptions:\n", $formatter->getHelpText(array()));

        $formatter->setScriptName('test');
        $this->assertSame("My custom Banner test\nOptions:\n", $formatter->getHelpText(array()));
    }
}
