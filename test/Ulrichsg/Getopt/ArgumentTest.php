<?php

namespace Ulrichsg\Getopt;

class ArgumentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $argument1 = new Argument();
        $argument2 = new Argument(10);
        $this->assertFalse($argument1->hasDefaultValue());
        $this->assertEquals(10, $argument2->getDefaultValue());
    }

    public function testSetDefaultValueNotScalar()
    {
        $this->setExpectedException('InvalidArgumentException');
        $argument = new Argument();
        $argument->setDefaultValue(array());
    }
}