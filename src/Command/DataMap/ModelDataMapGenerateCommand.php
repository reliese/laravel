<?php
/** @noinspection PhpMissingFieldTypeInspection */

namespace Reliese\Command\DataMap;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Reliese\Analyser\AnalyserFactory;
use Reliese\Coders\Model\Factory;
use Reliese\Command\ConfigurationProfileOptionTrait;
use Reliese\Configuration\RelieseConfigurationFactory;
use Reliese\Generator\DataAttribute\DataAttributeGenerator;
use Reliese\Generator\DataMap\ModelDataMapGenerator;
use Reliese\Generator\DataTransport\DataTransportObjectGenerator;
use Reliese\Generator\Model\ModelGenerator;

/**
 * Class ModelDataMapGenerateCommand
 */
class ModelDataMapGenerateCommand extends Command
{
    use ConfigurationProfileOptionTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reliese:map:model:generate
                            {--s|schema= : The name of the MySQL database}
                            {--c|connection= : The name of the connection}
                            {--t|table= : The name of the table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Blueprints from the specified connection, schema, and table then output them to specified path.';

    /**
     * @var \Reliese\Coders\Model\Factory
     */
    protected $models;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Create a new command instance.
     *
     * @param \Reliese\Coders\Model\Factory $models
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(
        Factory $models,
        Repository $config)
    {
        $this->signature .= self::$configurationProfileOptionDescription;
        parent::__construct();

        $this->models = $models;
        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @param AnalyserFactory $analyserFactory
     * @param RelieseConfigurationFactory $relieseConfigurationFactory
     */
    public function handle(
        AnalyserFactory $analyserFactory,
        RelieseConfigurationFactory $relieseConfigurationFactory,
    ) {
        $relieseConfiguration = $relieseConfigurationFactory->getRelieseConfiguration($this->getConfigurationProfileName());
        $connection = $this->getConnection();
        $schema = $this->getSchema($connection);
        $table = $this->getTable();

        /*
         * TODO: allow command line options to modify state of the $relieseConfiguration graph
         */

        /*
         * Create the correct analyser for the configuration profile
         */
        $databaseAnalyser =  $analyserFactory->databaseAnalyser(
            $relieseConfiguration
        );

        /*
         * Allow the $databaseAnalyser to create the Database Blueprint
         */
        $databaseBlueprint = $databaseAnalyser->analyseDatabase(
            $relieseConfiguration->getDatabaseBlueprintConfiguration()
        );

        $schemaBlueprint = $databaseBlueprint->getSchemaBlueprint($schema);

        /*
         * Create a ModelDataMapGenerator
         */
        $modelDataMapGenerator = new ModelDataMapGenerator(
            $relieseConfiguration->getModelDataMapGeneratorConfiguration(),
            $relieseConfiguration->getDataTransportGeneratorConfiguration(),
            new ModelGenerator($relieseConfiguration->getModelGeneratorConfiguration()),
            new DataTransportObjectGenerator(
                $relieseConfiguration->getDataTransportGeneratorConfiguration(),
                new DataAttributeGenerator($relieseConfiguration->getDataAttributeGeneratorConfiguration())
            ),
        );

        if (!empty($table)) {
            // Generate only for the specified table
            $tableBlueprint = $schemaBlueprint->getTableBlueprint($table);
            $modelDataMapGenerator->fromTableBlueprint($tableBlueprint);
            return;
        }

        /*
         * Display the data that would be used to perform code generation
         */
        foreach ($schemaBlueprint->getTableBlueprints() as $tableBlueprint) {
            $modelDataMapGenerator->fromTableBlueprint($tableBlueprint);
        }
    }

    /**
     * @return string
     */
    protected function getConnection()
    {
        return $this->option('connection') ?: $this->config->get('database.default');
    }

    /**
     * @param $connection
     *
     * @return string
     */
    protected function getSchema($connection)
    {
        return $this->option('schema') ?: $this->config->get("database.connections.$connection.database");
    }

    /**
     * @return string
     */
    protected function getTable()
    {
        return $this->option('table');
    }
}
