<?php

namespace GetOpt\Test\Options;

use GetOpt\GetOpt;
use GetOpt\Option;
use PHPUnit\Framework\TestCase;

class HelpTest extends TestCase
{
    public function testHelpText()
    {
        $getopt = new GetOpt([
            [ 'a', 'alpha', GetOpt::NO_ARGUMENT, 'Short and long options with no argument' ],
            [ null, 'beta', GetOpt::OPTIONAL_ARGUMENT, 'Long option only with an optional argument' ],
            [ 'c', null, GetOpt::REQUIRED_ARGUMENT, 'Short option only with a mandatory argument' ]
        ]);
        $getopt->process('');

        $script = $_SERVER['PHP_SELF'];

        $expected = "Usage: $script [options] [operands]\n";
        $expected .= "Options:\n";
        $expected .= "  -a, --alpha     Short and long options with no argument\n";
        $expected .= "  --beta [<arg>]  Long option only with an optional argument\n";
        $expected .= "  -c <arg>        Short option only with a mandatory argument\n";

        self::assertSame($expected, $getopt->getHelpText());
    }

    public function testHelpTextWithoutDescriptions()
    {
        $getopt = new GetOpt([
            [ 'a', 'alpha', GetOpt::NO_ARGUMENT ],
            [ null, 'beta', GetOpt::OPTIONAL_ARGUMENT ],
            [ 'c', null, GetOpt::REQUIRED_ARGUMENT ]
        ]);
        $getopt->process('');

        $script = $_SERVER['PHP_SELF'];

        $expected = "Usage: $script [options] [operands]\n";
        $expected .= "Options:\n";
        $expected .= "  -a, --alpha     \n";
        $expected .= "  --beta [<arg>]  \n";
        $expected .= "  -c <arg>        \n";

        self::assertSame($expected, $getopt->getHelpText());
    }

    public function testHelpTextWithLongDescriptions()
    {
        defined('COLUMNS') || define('COLUMNS', 90);
        $getopt = new GetOpt([
            [
                'a', 'alpha', GetOpt::NO_ARGUMENT, 'Short and long options with no argument and a very long text ' .
                                                   'that exceeds the length of the row'
            ],
            [ null, 'beta', GetOpt::OPTIONAL_ARGUMENT, 'Long option only with an optional argument' ],
            [ 'c', null, GetOpt::REQUIRED_ARGUMENT, 'Short option only with a mandatory argument' ]
        ]);
        $getopt->process('');

        $script = $_SERVER['PHP_SELF'];

        $expected = "Usage: $script [options] [operands]\n" .
                    "Options:\n" .
                    "  -a, --alpha     Short and long options with no argument and a very long text that\n" .
                    "                  exceeds the length of the row\n" .
                    "  --beta [<arg>]  Long option only with an optional argument\n" .
                    "  -c <arg>        Short option only with a mandatory argument\n";

        self::assertSame($expected, $getopt->getHelpText());
    }

    public function testHelpTextWithArgumentName()
    {
        $getopt = new GetOpt([
            Option::create('a', 'alpha', GetOpt::REQUIRED_ARGUMENT)
                ->setArgumentName('alpha')
        ]);

        $script = $_SERVER['PHP_SELF'];
        self::assertSame(
            'Usage: ' . $script . ' [options] [operands]' . PHP_EOL .
            'Options:' . PHP_EOL .
            '  -a, --alpha <alpha>  ' . PHP_EOL,
            $getopt->getHelpText()
        );
    }
}
