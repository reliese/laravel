<?php

namespace Reliese\Command;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Reliese\Analyser\AnalyserFactory;
use Reliese\Configuration\RelieseConfigurationFactory;
use Reliese\MetaCode\Writers\CodeWriter;
/**
 * Class AbstractGenerationCommand
 */
abstract class AbstractGenerationCommand extends Command
{
    use ConfigurationProfileOptionTrait;

    /**
     * The name and signature of the console command.
     * @see Keep in sync with \Reliese\Coders\Console\CodeModelsCommand::$signature
     * @var string
     */
    protected $signature = null;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse connection schema into models';

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;


    protected function __construct(Repository $config, string $commandName, string $description)
    {
        $this->signature = sprintf('%s %s
                            {--s|schema= : The name of the MySQL database}
                            {--c|connection= : The name of the connection}
                            {--t|table= : The name of the table}', $commandName, $description);
        parent::__construct();

        $this->config = $config;
    }


    /**
     * Execute the console command.
     *
     * @param AnalyserFactory $analyserFactory
     * @param RelieseConfigurationFactory $relieseConfigurationFactory
     * @todo clean this method
     */
    public function handle(
        AnalyserFactory $analyserFactory,
        RelieseConfigurationFactory $relieseConfigurationFactory,
    ) {
        $relieseConfiguration = $relieseConfigurationFactory->getRelieseConfiguration($this->getConfigurationProfileName());
        $connection = $this->getConnection();
        $schema = $this->getSchema($connection);
        $tableNameFilter = $this->getTable();

        /*
         * TODO: allow command line options to modify state of the $relieseConfiguration graph
         */

        /*
         * Create the correct analyser for the configuration profile
         */
        $databaseAnalyser =  $analyserFactory->databaseAnalyser($relieseConfiguration);

        /*
         * Allow the $databaseAnalyser to create the Database Blueprint
         */
        $databaseBlueprint = $databaseAnalyser->analyseDatabase($relieseConfiguration->getDatabaseBlueprintConfiguration());

        // TODO: Apply Command Line options that override the configuration values
        $generator = $this->initializeGenerator($relieseConfiguration);

        $schemaBlueprint = $databaseBlueprint->getSchemaBlueprint($schema);

        $codeWriter = new CodeWriter($relieseConfiguration);

        /*
         * Display the data that would be used to perform code generation
         */
        foreach ($schemaBlueprint->getTableBlueprints() as $tableBlueprint) {
            if (!empty($tableNameFilter) && $tableNameFilter !== $tableBlueprint->getName()) {
                continue;
            }
            // Generate only for the specified table
            $tableBlueprint = $schemaBlueprint->getTableBlueprint($tableNameFilter);
            $codeWriter->overwriteClassDefinition(
                $generator->generateClass($tableBlueprint)
            );
            $codeWriter->createClassDefinition(
                $generator->generateAbstractClass($tableBlueprint)
            );
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