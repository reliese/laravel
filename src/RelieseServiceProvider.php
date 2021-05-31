<?php

namespace Reliese;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Reliese\Coders\Console\CodeModelsCommand;
use Reliese\Coders\Model\Config;
use Reliese\Coders\Model\Factory as ModelFactory;
use Reliese\Command\Blueprint\ShowBlueprintCommand;
use Reliese\Command\DataAccess\DataAccessGenerateCommand;
use Reliese\Command\DataAttribute\DataAttributeGenerateCommand;
use Reliese\Command\DataMap\ModelDataMapGenerateCommand;
use Reliese\Command\DataTransport\DataTransportGenerateCommand;
use Reliese\Command\Model\ModelGenerateCommand;
use Reliese\Command\Model\NewModelGenerateCommand;
use Reliese\Command\Validator\DtoValidatorGenerateCommand;
use Reliese\Configuration\ConfigurationProfile;
use Reliese\Configuration\ConfigurationProfileFactory;
use Reliese\Support\Classify;
use function realpath;
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

    private bool $loadReliese = false;

    public function __construct($app)
    {
        parent::__construct($app);

        $this->loadReliese = $app->environment('local') && $app->runningInConsole();
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!$this->loadReliese) {
            return;
        }

            $this->publishes([
                \Reliese\PackagePaths::getExampleModelConfigFilePath() => config_path('models.php'),
            ], 'reliese-models');

            $this->publishes([
                \Reliese\PackagePaths::getExampleConfigFilePath() => config_path('reliese.php'),
            ], 'reliese');

            $this->commands([
                CodeModelsCommand::class,
                ModelGenerateCommand::class,
//                NewModelGenerateCommand::class,
                ShowBlueprintCommand::class,
                ModelDataMapGenerateCommand::class,
                DataTransportGenerateCommand::class,
                DataAttributeGenerateCommand::class,
                DataAccessGenerateCommand::class,
                DtoValidatorGenerateCommand::class,
            ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (!$this->loadReliese) {
            return;
        }

        $this->app->singleton(ConfigurationProfileFactory::class, function (Application $app) {
            return new ConfigurationProfileFactory(
                realpath($app->path()),
                $app->make('config')->get('reliese')
            );
        });

        $this->app->bind(
            ConfigurationProfile::class,
            function(Application $app) {
                /** @var ConfigurationProfileFactory $configurationProfileFactory */
                $configurationProfileFactory = $app->get(ConfigurationProfileFactory::class);
                if (!$configurationProfileFactory->hasActiveConfigurationProfile()) {
                    throw new \RuntimeException("An active configuration profile has not been specified.");
                }
                return $configurationProfileFactory->getActiveConfigurationProfile();
            },
            true
        );

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
        if (!$this->loadReliese) {
            return [];
        }

        return [
            ConfigurationProfileFactory::class,
            ConfigurationProfile::class,
            ModelFactory::class,
        ];
    }

}
