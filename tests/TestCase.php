<?php

class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown()
    {
        if (class_exists('Mockery')) {
            Mockery::close();
        }
    }
}
