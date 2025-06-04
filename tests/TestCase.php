<?php

namespace Reliese\Tests;

use Illuminate\Contracts\Config\Repository;
use Mockery;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Reliese\Coders\CodersServiceProvider;

/**
 * Created by Cristian.
 * Date: 16/10/16 12:49 PM.
 */
class TestCase extends OrchestraTestCase
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

    protected function getAnnotations()
    {
        return [];
    }

    protected function getPackageProviders($app)
    {
        return [CodersServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        tap($app['config'], function (Repository $config) {
            // example
            // $config->set('database.default', 'testbench');
        });
    }
}
