<?php

namespace Reliese\Coders;

use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Reliese\Analyser\AnalyserFactory;
use Reliese\Blueprint\BlueprintFactory;
use Reliese\Command\Blueprint\ShowBlueprintCommand;
use Reliese\Command\DataTransport\DataTransportGenerateCommand;
use Reliese\Command\Model\MakeModelsCommand;
use Reliese\Support\Classify;
use Reliese\Coders\Model\Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Reliese\Coders\Console\CodeModelsCommand;
use Reliese\Coders\Model\Factory as ModelFactory;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CodersServiceProvider extends ServiceProvider
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
                __DIR__.'/../../config/models.php' => config_path('models.php'),
            ], 'reliese-models');

            $this->commands([
                CodeModelsCommand::class,
                MakeModelsCommand::class,
                ShowBlueprintCommand::class,
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
        $this->registerModelFactory();
    }

    /**
     * Register Model Factory.
     *
     * @return void
     */
    protected function registerModelFactory()
    {
        /** @var Config $config */
        $config = new Config($this->app->make('config')->get('models'));

        /** @var DatabaseManager $laravelDatabaseManager */
        $laravelDatabaseManager = $this->app->make('db');

        $this->app->singleton(OutputInterface::class, function ($app) {
                return $app->make(ConsoleOutput::class);
            }
        );

        $this->app->singleton(AnalyserFactory::class,
            function ($app) {
                return new AnalyserFactory(
                    $app->make(ConnectionFactory::class)
                );
            }
        );

        $this->app->singleton(BlueprintFactory::class, function ($app) use ($config, $laravelDatabaseManager) {
            return new BlueprintFactory(
                $app->make(AnalyserFactory::class),
                $laravelDatabaseManager,
                $config,
                $app->make(OutputInterface::class)
            );
        });

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
        return [ModelFactory::class];
    }
}
