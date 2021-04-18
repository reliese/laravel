<?php

namespace Reliese;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Reliese\Coders\Console\CodeModelsCommand;
use Reliese\Coders\Model\Config;
use Reliese\Coders\Model\Factory as ModelFactory;
use Reliese\Command\Blueprint\ShowBlueprintCommand;
use Reliese\Command\DataMap\ModelDataMapGenerateCommand;
use Reliese\Command\DataTransport\DataTransportGenerateCommand;
use Reliese\Command\Model\ModelGenerateCommand;
use Reliese\Command\Model\NewModelGenerateCommand;
use Reliese\Configuration\RelieseConfigurationFactory;
use Reliese\Support\Classify;
use const DIRECTORY_SEPARATOR;

/**
 * Class RelieseServiceProvider
 */
class RelieseServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                \Reliese\PackagePaths::getExampleModelConfigFilePath() => config_path('models.php'),
            ], 'reliese-models');

            $this->publishes([
                \Reliese\PackagePaths::getExampleConfigFilePath() => config_path('reliese.php'),
            ], 'reliese');

            $this->commands([
                CodeModelsCommand::class,
                ModelGenerateCommand::class,
                NewModelGenerateCommand::class,
                ShowBlueprintCommand::class,
                ModelDataMapGenerateCommand::class,
                DataTransportGenerateCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RelieseConfigurationFactory::class, function (Application $app) {
            return new RelieseConfigurationFactory(
                realpath($app->path()),
                realpath($app->path().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'),
                $app->make('config')->get('reliese')
            );
        });

        // legacy model factory
        $this->app->singleton(ModelFactory::class, function ($app) {
            return new ModelFactory(
                $app->make('db'),
                $app->make(Filesystem::class),
                new Classify(),
                new Config($app->make('config')->get('models'))
            );
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            RelieseConfigurationFactory::class,
            ModelFactory::class,
        ];
    }

}
