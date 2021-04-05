<?php
/** @noinspection PhpMissingFieldTypeInspection */

namespace Reliese\Command\DataTransport;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Reliese\Blueprint\BlueprintFactory;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\SchemaBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Coders\Model\Factory;
use Reliese\Configuration\DataTransportGenerationConfiguration;
use Reliese\Generator\DataTransport\DataTransportGenerator;
/**
 * Class DataTransportGenerateCommand
 */
class DataTransportGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reliese:dto:generate
                            {--p|path= : The folder where Data Transport Objects should be generated}
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
    public function __construct(Factory $models, Repository $config)
    {
        parent::__construct();

        $this->models = $models;
        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @param BlueprintFactory $blueprintFactory
     */
    public function handle(BlueprintFactory $blueprintFactory)
    {
        $connection = $this->getConnection();
        $schema = $this->getSchema($connection);
        $table = $this->getTable();

        $this->output->writeln("");

        /*
         * Generate the raw blueprints
         */
        $databaseBlueprint = $blueprintFactory->database($connection);

        /*
         * TODO: Implement Blueprint Modification via Blueprint Configuration
         * Apply configurations that modify the blueprints
         */
        //        $blueprintConfiguration = new BlueprintConfiguration();
        //        $blueprintConfiguration->applyConfiguration($databaseBlueprint);

        $dataTransportGenerationConfiguration = new DataTransportGenerationConfiguration();

        $dataTransportGenerator = new DataTransportGenerator(
            $dataTransportGenerationConfiguration
        );

        $schemaBlueprint = $databaseBlueprint->getSchemaBlueprint($schema);

        if (!empty($table)) {
            // Generate only for the specified table
            $tableBlueprint = $schemaBlueprint->getTableBlueprint($table);
            $this->generate($dataTransportGenerator, $databaseBlueprint, $schemaBlueprint, $tableBlueprint);
            return;
        }

        /*
         * Display the data that would be used to perform code generation
         */
        foreach ($schemaBlueprint->getTableBlueprints() as $tableBlueprint) {
            $this->generate($dataTransportGenerator, $databaseBlueprint, $schemaBlueprint, $tableBlueprint);
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

    private function generate(
        DataTransportGenerator $dataTransportGenerator,
        DatabaseBlueprint $databaseBlueprint,
        SchemaBlueprint $schemaBlueprint,
        TableBlueprint $tableBlueprint
    ) {
        $result = $dataTransportGenerator->fromTableBlueprint($tableBlueprint);
    }
}
