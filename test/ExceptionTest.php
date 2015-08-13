<?php

namespace Oxygen\Tests;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionsCodesAreUnique()
    {
        $reflectionClass = new \ReflectionClass(\Oxygen_Exception::class);
        $constants = $reflectionClass->getConstants();

        $this->assertEquals($constants, array_unique($constants));
    }
}
