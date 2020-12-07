<?php

/**
 * Created by Cristian.
 * Date: 16/10/16 12:49 PM.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown(): void
    {
        if (class_exists('Mockery')) {
            Mockery::close();
        }
    }
}
