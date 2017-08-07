<?php

namespace GetOpt\Test\Options;

use GetOpt\GetOpt;
use PHPUnit\Framework\TestCase;

class NonStrictTest extends TestCase
{
    /** @var GetOpt */
    protected $getopt;

    protected function setUp()
    {
        $this->getopt = new GetOpt(null, [
            GetOpt::SETTING_STRICT_OPTIONS => false,
        ]);
    }

    public function testAdditionalOptionsDoNotThrow()
    {
        $this->getopt->process('-a --beta');

        $this->addToAssertionCount(1); // it did not throw - that is positive
    }

    public function testStoresTheArgument()
    {
        $this->getopt->process('-a aValue --beta betaValue -ccValue');

        self::assertSame([
            'a' => 'aValue',
            'beta' => 'betaValue',
            'c' => 'cValue',
        ], $this->getopt->getOptions());
        self::assertSame('aValue', $this->getopt->getOption('a'));
    }

    public function testAdditionalOptionsAreResetted()
    {
        $this->getopt->process('-a aValue --beta betaValue -ccValue');

        $this->getopt->process('');

        self::assertSame([], $this->getopt->getOptions());
    }

    public function testIteratesOverAdditionalOptions()
    {
        $this->getopt->process('-a aValue --beta betaValue');

        self::assertSame([
            'a' => 'aValue',
            'beta' => 'betaValue'
        ], iterator_to_array($this->getopt->getIterator()));
    }

    public function testOffsetExists()
    {
        $this->getopt->process('--alpha alphaValue');

        self::assertTrue($this->getopt->offsetExists('alpha'));
    }

    public function testOffsetGet()
    {
        $this->getopt->process('--alpha alphaValue');

        self::assertSame('alphaValue', $this->getopt['alpha']);
    }

    public function testStoresTheCountWithoutValue()
    {
        $this->getopt->process('-a -a -a');

        self::assertSame(3, $this->getopt->getOption('a'));
    }

    public function testShowsOptionsInUsage()
    {
        $script = $_SERVER['PHP_SELF'];

        self::assertSame(
            'Usage: ' . $script . ' [options] [operands]' . PHP_EOL,
            $this->getopt->getHelpText()
        );
    }
}
