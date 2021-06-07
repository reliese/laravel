<?php

namespace Reliese;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Reliese\Analyser\DatabaseAnalyserInterface;
use Reliese\Analyser\Doctrine\DatabaseVendorAdapterInterface;
use Reliese\Analyser\Doctrine\DoctrineDatabaseAnalyser;
use Reliese\Coders\Console\CodeModelsCommand;
use Reliese\Coders\Model\Config;
use Reliese\Coders\Model\Factory as ModelFactory;
use Reliese\Command\Blueprint\ShowBlueprintCommand;
use Reliese\Command\DataAccess\GenerateDataAccessCommand;
use Reliese\Command\DataMap\GenerateModelDataMappingCommand;
use Reliese\Command\DataTransport\GenerateDataTransportObjectsCommand;
use Reliese\Command\GenerateAllCommand;
use Reliese\Command\Model\ModelGenerateCommand;
use Reliese\Command\Validator\GenerateDtoValidationCommand;
use Reliese\Configuration\ConfigurationProfile;
use Reliese\Configuration\ConfigurationProfileFactory;
use Reliese\Configuration\Sections\FileSystemConfiguration;
use Reliese\Database\PhpTypeMappingInterface;
use Reliese\Database\TypeMappings\MySqlDataTypeMap;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Format\CodeFormatter;
use Reliese\MetaCode\Format\IndentationProvider;
use Reliese\Support\Classify;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use function realpath;

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
                GenerateModelDataMappingCommand::class,
                GenerateDataTransportObjectsCommand::class,
                GenerateDataAccessCommand::class,
                GenerateDtoValidationCommand::class,
                GenerateAllCommand::class,
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

        $getActiveConfigurationProfile = function(Application $app): ConfigurationProfile {
            /**
             * Gets the active configuration profile (based on commandline option '-p')
             *
             * @var ConfigurationProfileFactory $configurationProfileFactory
             */
            $configurationProfileFactory = $app->get(ConfigurationProfileFactory::class);
            if (!$configurationProfileFactory->hasActiveConfigurationProfile()) {
                throw new \RuntimeException("An active configuration profile has not been specified.");
            }
            return $configurationProfileFactory->getActiveConfigurationProfile();
        };

        $this->app->singleton(
            FileSystemConfiguration::class,
            function(Application $app) {
                return new FileSystemConfiguration(
                    [
                        FileSystemConfiguration::APPLICATION_PATH => realpath($app->basePath('app')),
                        FileSystemConfiguration::BASE_PATH => realpath($app->basePath()),
                    ]
                );
            }
        );

        $this->app->singleton(ConfigurationProfileFactory::class, function (Application $app) {
            $configurations = $app->make('config')->get('reliese');

            return new ConfigurationProfileFactory(
                $app->make(FileSystemConfiguration::class),
                $configurations
            );
        });

        $this->app->bind(PhpTypeMappingInterface::class,
            function(Application $app) {

                /** @var ConfigurationProfile $configurationProfile */
                $configurationProfile = $app->make(ConfigurationProfile::class);

                /** @var DatabaseManager $databaseManager */
                $databaseManager = $app->get(DatabaseManager::class);

                $connection = $databaseManager->connection($configurationProfile->getDatabaseAnalyserConfiguration()
                    ->getConnectionName());

                switch ($connection->getDriverName()) {
                    case 'mysql':
                        return $app->make(MySqlDataTypeMap::class);
                    default:
                        throw new \RuntimeException(
                            sprintf(
                                "PhpTypeMapping is not defined for database driver \"{%s}\"",
                                $connection->getDriverName()
                            )
                        );
                }
            },
            true
        );

        $this->app->bind(ConfigurationProfile::class, $getActiveConfigurationProfile, false);

        $this->app->bind(
            DatabaseVendorAdapterInterface::class,
            function(Application $app) use ($getActiveConfigurationProfile) {
                /*
                 * Returns the Database Vendor Adapter based on the active configuration profile
                 */
                $configurationProfile = $getActiveConfigurationProfile($app);
                return $app->make(
                    $configurationProfile->getDatabaseAnalyserConfiguration()->getDatabaseVendorAdapterClass()
                );
            },
            false
        );

        $this->app->bind(
            DatabaseAnalyserInterface::class,
            function(Application $app) {

                return new DoctrineDatabaseAnalyser(
                    $app->get(DatabaseManager::class),
                    $app->make(ConfigurationProfile::class),
                    $app->make(DatabaseVendorAdapterInterface::class)
                );
            },
            true
        );

        $this->app->bind(
            CodeFormatter::class,
            function(Application $app) {
                return new CodeFormatter($app->make(IndentationProvider::class));
            },
            false
        );

        $this->app->bind(
            IndentationProvider::class,
            function(Application $app) {
                return IndentationProvider::fromConfig($app->make(ConfigurationProfile::class));
            },
            false
        );

        $this->app->singleton(
            OutputInterface::class,
            function(Application $app) {
                return new ConsoleOutput();
            }
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
            DatabaseAnalyserInterface::class,
            ConfigurationProfileFactory::class,
            ConfigurationProfile::class,
            ModelFactory::class,
        ];
    }

}
