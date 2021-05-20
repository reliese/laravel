<?php

namespace Reliese\Command\Model;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Reliese\Analyser\AnalyserFactory;
use Reliese\Blueprint\BlueprintFactory;
use Reliese\Coders\Model\Factory;
use Reliese\Command\ConfigurationProfileOptionTrait;
use Reliese\Configuration\ModelGeneratorConfiguration;
use Reliese\Configuration\RelieseConfiguration;
use Reliese\Configuration\RelieseConfigurationFactory;
use Reliese\Generator\Model\ModelGenerator;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Writers\CodeWriter;

/**
 * Class NewModelGenerateCommand
 */
class NewModelGenerateCommand extends Command
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
     * @var \Reliese\Coders\Model\Factory
     */
    protected $models;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var ClassFormatter
     */
    private ClassFormatter $classFormatter;

    /**
     * @var CodeWriter
     */
    private CodeWriter $codeWriter;

    /**
     * Create a new command instance.
     *
     * @param \Reliese\Coders\Model\Factory $models
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(Factory $models, Repository $config)
    {
        $this->signature = 'reliese:model:generate'.(self::$configurationProfileOptionDescription).'
                            {--s|schema= : The name of the MySQL database}
                            {--c|connection= : The name of the connection}
                            {--t|table= : The name of the table}';
        parent::__construct();

        $this->models = $models;
        $this->config = $config;
        $this->codeWriter = new CodeWriter();
        $this->classFormatter = new ClassFormatter();
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
        $table = $this->getTable();

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
        $modelGenerator = new ModelGenerator($relieseConfiguration);

        $schemaBlueprint = $databaseBlueprint->getSchemaBlueprint($schema);

        if (!empty($table)) {
            // Generate only for the specified table
            $tableBlueprint = $schemaBlueprint->getTableBlueprint($table);
            $this->writeClassFiles(
                $modelGenerator,
                $modelGenerator->generateModelClass($tableBlueprint),
                $modelGenerator->generateModelAbstractClass($tableBlueprint)
            );
            return;
        }

        /*
         * Display the data that would be used to perform code generation
         */
        foreach ($schemaBlueprint->getTableBlueprints() as $tableBlueprint) {
            $this->writeClassFiles(
                $modelGenerator,
                $modelGenerator->generateModelClass($tableBlueprint),
                $modelGenerator->generateModelAbstractClass($tableBlueprint)
            );
        }
    }

    protected function writeClassFiles(
        \Reliese\Generator\Model\ModelGenerator $modelGenerator,
        ClassDefinition $modelClass,
        ClassDefinition $abstractModelClass
    ) {
        $this->codeWriter->overwriteClassDefinition(
            $modelGenerator->getAbstractModelClassFilePath($abstractModelClass),
            $this->classFormatter->format($abstractModelClass)
        );

        $this->codeWriter->createClassDefinition(
            $modelGenerator->getModelClassFilePath($modelClass),
            $this->classFormatter->format($modelClass)
        );
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
