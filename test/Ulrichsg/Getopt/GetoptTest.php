<?php

namespace Ulrichsg\Getopt;

class GetoptTest extends \PHPUnit_Framework_TestCase
{
    public function testParseNoOptions() {
        $getopt = new Getopt('a');
        $getopt->parse('something');
        $this->assertNull($getopt->getOption('a'));
        $operands = $getopt->getOperands();
        $this->assertInternalType('array', $operands);
        $this->assertCount(1, $operands);
        $this->assertEquals('something', $operands[0]);
    }

    public function testParseUnknownOption() {
        $this->setExpectedException('UnexpectedValueException');
        $getopt = new Getopt('a');
        $getopt->parse('-b');
    }

    public function testParseRequiredArgumentMissing() {
        $this->setExpectedException('UnexpectedValueException');
        $getopt = new Getopt('a:');
        $getopt->parse('-a');
    }

    public function testParseMultipleOptionsWithOneHyphen() {
        $getopt = new Getopt('abc');
        $getopt->parse('-abc');
        $this->assertEquals(1, $getopt->getOption('a'));
        $this->assertEquals(1, $getopt->getOption('b'));
        $this->assertEquals(1, $getopt->getOption('c'));
    }

    public function testParseCumulativeOption() {
        $getopt = new Getopt('ab');
        $getopt->parse('-a -b -a -a');
        $this->assertEquals(3, $getopt->getOption('a'));
        $this->assertEquals(1, $getopt->getOption('b'));
    }

    public function testParseCumulativeOptionShort() {
        $getopt = new Getopt('ab');
        $getopt->parse('-abaa');
        $this->assertEquals(3, $getopt->getOption('a'));
        $this->assertEquals(1, $getopt->getOption('b'));
    }

    public function testParseShortOptionWithArgument() {
        $getopt = new Getopt('a:');
        $getopt->parse('-a value');
        $this->assertEquals('value', $getopt->getOption('a'));
    }

	public function testParseNumericOption() {
		$getopt = new Getopt('a:2');
		$getopt->parse('-a 2 -2');
		$this->assertEquals('2', $getopt->getOption('a'));
		$this->assertEquals(1, $getopt->getOption('2'));
	}

    public function testParseCollapsedShortOptionsRequiredArgumentMissing() {
        $this->setExpectedException('UnexpectedValueException');
        $getopt = new Getopt('ab:');
        $getopt->parse('-ab');
    }

    public function testParseCollapsedShortOptionsWithArgument() {
        $getopt = new Getopt('ab:');
        $getopt->parse('-ab value');
        $this->assertEquals(1, $getopt->getOption('a'));
        $this->assertEquals('value', $getopt->getOption('b'));
    }

    public function testParseNoArgumentOptionAndOperand() {
        $getopt = new Getopt('a');
        $getopt->parse('-a b');
        $this->assertEquals(1, $getopt->getOption('a'));
        $operands = $getopt->getOperands();
        $this->assertInternalType('array', $operands);
        $this->assertCount(1, $operands);
        $this->assertEquals('b', $operands[0]);
    }

    public function testParseOperandsOnly() {
        $getopt = new Getopt('a:b');
        $getopt->parse('-- -a -b');
        $this->assertNull($getopt->getOption('a'));
        $this->assertNull($getopt->getOption('b'));
        $operands = $getopt->getOperands();

        $this->assertInternalType('array', $operands);
        $this->assertCount(2, $operands);
        $this->assertEquals('-a', $operands[0]);
        $this->assertEquals('-b', $operands[1]);

	    $this->assertTrue($getopt->hasOperands());
	    $this->assertEquals(2, $getopt->getOperandCount());
	    $this->assertEquals('-a', $getopt->getOperand(0));
	    $this->assertEquals('-b', $getopt->getOperand(1));
    }

    public function testParseLongOptionWithoutArgument() {
        $getopt = new Getopt(array(
            array('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $getopt->parse('--option');
        $this->assertEquals(1, $getopt->getOption('option'));
    }

    public function testParseLongOptionWithoutArgumentAndOperand() {
        $getopt = new Getopt(array(
            array('o', 'option', Getopt::NO_ARGUMENT)
        ));
        $getopt->parse('--option something');
        $this->assertEquals(1, $getopt->getOption('option'));
        $operands = $getopt->getOperands();
        $this->assertInternalType('array', $operands);
        $this->assertCount(1, $operands);
        $this->assertEquals('something', $operands[0]);
    }

    public function testParseLongOptionWithArgument() {
        $getopt = new Getopt(array(
            array('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $getopt->parse('--option value');
        $this->assertEquals('value', $getopt->getOption('option'));
        $this->assertEquals('value', $getopt->getOption('o'));
    }

    public function testParseLongOptionWithEqualsSignAndArgument() {
        $getopt = new Getopt(array(
            array('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $getopt->parse('--option=value something');
        $this->assertEquals('value', $getopt->getOption('option'));
        $operands = $getopt->getOperands();
        $this->assertInternalType('array', $operands);
        $this->assertCount(1, $operands);
        $this->assertEquals('something', $operands[0]);
    }

    public function testParseLongOptionWithValueStartingWithHyphen() {
        $getopt = new Getopt(array(
            array('o', 'option', Getopt::REQUIRED_ARGUMENT)
        ));
        $getopt->parse('--option=-value');
        $this->assertEquals('-value', $getopt->getOption('option'));
    }

    public function testParseNoValueStartingWithHyphenRequired() {
        $this->setExpectedException('UnexpectedValueException');
        $getopt = new Getopt('a:b');
        $getopt->parse('-a -b');
    }

    public function testParseNoValueStartingWithHyphenOptional() {
        $getopt = new Getopt('a::b');
        $getopt->parse('-a -b');
        $this->assertEquals(1, $getopt->getOption('a'));
        $this->assertEquals(1, $getopt->getOption('b'));
    }

	public function testParseOptionWithDefaultValue() {
        echo "important test\n";
		$getopt = new Getopt(array(
			array('a', null, Getopt::REQUIRED_ARGUMENT, 'alpha', 10),
			array('b', 'beta', Getopt::REQUIRED_ARGUMENT, 'beta', 20)
		));
		$getopt->parse('-a 12');
		$this->assertEquals(12, $getopt->getOption('a'));
		$this->assertEquals(20, $getopt->getOption('b'));
		$this->assertEquals(20, $getopt->getOption('beta'));
	}

    public function testAddOptions ()
    {
        $getopt = new Getopt();
        $getopt->addOptions('a:');
        $getopt->addOptions(array(
            array('s', null, Getopt::OPTIONAL_ARGUMENT),
            array(null, 'long', Getopt::OPTIONAL_ARGUMENT),
            array('n', 'name', Getopt::OPTIONAL_ARGUMENT)
        ));

        $getopt->parse('-a aparam -s sparam --long longparam');
        $this->assertCount(4, $getopt->getOptionList());
        $this->assertEquals('aparam', $getopt->getOption('a'));
        $this->assertEquals('longparam', $getopt->getOption('long'));
        $this->assertEquals('sparam', $getopt->getOption('s'));
    }

	public function testAddOptionsChooseShortOrLongAutomatically() {
		$getopt = new Getopt();
        $getopt->addOptions(array(
            array('s'),
            array('long', Getopt::OPTIONAL_ARGUMENT)
        ));

        $getopt->parse('-s --long longparam');
        $this->assertEquals('longparam', $getopt->getOption('long'));
        $this->assertEquals('1', $getopt->getOption('s'));
	}

	public function testAddOptionsUseDefaultArgumentType() {
		$getopt = new Getopt(null, Getopt::REQUIRED_ARGUMENT);
        $getopt->addOptions(array(
            array('l', 'long')
        ));

        $getopt->parse('-l something');
        $this->assertEquals('something', $getopt->getOption('l'));

		$getopt->parse('--long someOtherThing');
		$this->assertEquals('someOtherThing', $getopt->getOption('long'));
	}

	public function testParseZeroArgument() {
        $getopt = new Getopt('a:');
        $getopt->parse('-a 0');
        $this->assertEquals('0', $getopt->getOption('a'));
    }

    public function testDoubleHyphenNotInOperands()
    {
        $getopt = new Getopt('a:');
        $getopt->parse('-a 0 foo -- bar baz');
        $this->assertEquals('0', $getopt->getOption('a'));
        $operands = $getopt->getOperands();
        $this->assertInternalType('array', $operands);
        $this->assertCount(3, $operands);
        $this->assertEquals('foo', $operands[0]);
        $this->assertEquals('bar', $operands[1]);
        $this->assertEquals('baz', $operands[2]);
    }

    public function testCountable() {
		$getopt = new Getopt('abc');
		$getopt->parse('-a -b -c');
		$this->assertEquals(3, count($getopt));
	}

    public function testArrayAccess() {
		$getopt = new Getopt('q');
		$getopt->parse('-q');
		$this->assertEquals(1, $getopt['q']);
	}

    public function testIterable() {
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

    public function testHelpText() {
        $getopt = new Getopt(array(
            array('a', 'alpha', Getopt::NO_ARGUMENT, 'Short and long options with no argument'),
            array(null, 'beta', Getopt::OPTIONAL_ARGUMENT, 'Long option only with an optional argument'),
            array('c', null, Getopt::REQUIRED_ARGUMENT, 'Short option only with a mandatory argument')
        ));
        $getopt->parse('');

        $script = $_SERVER['PHP_SELF'];

        $expected  = "Usage: $script [options] [operands]\n";
        $expected .= "Options:\n";
        $expected .= "  -a, --alpha             Short and long options with no argument\n";
        $expected .= "  --beta [<arg>]          Long option only with an optional argument\n";
        $expected .= "  -c <arg>                Short option only with a mandatory argument\n";

        $this->assertEquals($expected, $getopt->getHelpText());
    }

    public function testHelpTextWithMissingDescriptions() {
        $getopt = new Getopt(array(
            array('a', 'alpha', Getopt::NO_ARGUMENT),
            array(null, 'beta', Getopt::OPTIONAL_ARGUMENT),
            array('c', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $getopt->parse('');

        $script = $_SERVER['PHP_SELF'];

        $expected  = "Usage: $script [options] [operands]\n";
        $expected .= "Options:\n";
        $expected .= "  -a, --alpha             \n";
        $expected .= "  --beta [<arg>]          \n";
        $expected .= "  -c <arg>                \n";

        $this->assertEquals($expected, $getopt->getHelpText());
    }
}
