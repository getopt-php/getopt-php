<?php

namespace Ulrichsg\Getopt;

class Getoptv2Test extends \PHPUnit_Framework_TestCase
{
    public function testAccessMethods()
    {
        $getopt = new Getoptv2('a');
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
        $getopt = new Getoptv2('abc');
        $getopt->parse('-abc');
        $this->assertEquals(3, count($getopt));
    }

    public function testArrayAccess()
    {
        $getopt = new Getoptv2('q');
        $getopt->parse('-q');
        $this->assertEquals(1, $getopt['q']);
    }

    public function testIterable()
    {
        $getopt = new Getoptv2(array(
                array(null, 'alpha', Getopt::NO_ARGUMENT),
                array('b', 'beta', Getopt::REQUIRED_ARGUMENT)
        ));
        $getopt->parse('--alpha -b foo');
        $expected = array('alpha' => 1, 'b' => 'foo'); // 'beta' should not occur
        foreach ($getopt as $option => $value) {
            $this->assertEquals($expected[$option], $value);
        }
    }
}
