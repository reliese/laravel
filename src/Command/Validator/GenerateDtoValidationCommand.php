<?php
/** @noinspection PhpMissingFieldTypeInspection */

namespace Reliese\Command\Validator;

use Reliese\Command\AbstractCodeGenerationCommand;

/**
 * Class ValidatorCodeGenerationCommand
 */
class ValidatorCodeGenerationDatabaseAnalysisCommandextends extends AbstractCodeGenerationCommand
{

    protected function initializeCodeGenerators(): array
    {
        return [
            ($this->getApplication()->get(DataTransportObjectClassGenerator::class)),
            ($this->getApplication()->get(DataTransportObjectAbstractClassGenerator::class)),
        ];
    }

    protected function getCommandName(): string
    {
        return 'generate:dto-validation';
    }

    protected function getCommandDescription(): string
    {
        return 'Generates Validators for Data Transport Objects';
    }
    //    use ConfigurationProfileOptionTrait;
//
//    /**
//     * The name and signature of the console command.
//     *
//     * @var string
//     */
//    protected $signature = 'reliese:validator:generate
//                            {--s|schema= : The name of the MySQL database}
//                            {--c|connection= : The name of the connection}
//                            {--t|table= : The name of the table}';
//
//    /**
//     * The console command description.
//     *
//     * @var string
//     */
//    protected $description = 'Generate Blueprints from the specified connection, schema, and table then output them to specified path.';
//
//    /**
//     * @var \Reliese\Coders\Model\Factory
//     */
//    protected Factory $models;
//
//    /**
//     * @var \Illuminate\Contracts\Config\Repository
//     */
//    protected Repository $config;
//
//    /**
//     * @var ClassFormatter
//     */
//    private ClassFormatter $classFormatter;
//
//    /**
//     * @var CodeWriter
//     */
//    private CodeWriter $codeWriter;
//
//    /**
//     * Create a new command instance.
//     *
//     * @param \Reliese\Coders\Model\Factory $models
//     * @param \Illuminate\Contracts\Config\Repository $config
//     */
//    public function __construct(Factory $models, Repository $config)
//    {
//        $this->signature .= self::$configurationProfileOptionDescription;
//        parent::__construct();
//
//        $this->models = $models;
//        $this->config = $config;
//    }
//
//    /**
//     * Execute the console command.
//     *
//     * @param AnalyserFactory $analyserFactory
//     * @param ConfigurationProfileFactory $configurationProfileFactory
//     */
//    public function handle(
//        AnalyserFactory $analyserFactory,
//        ConfigurationProfileFactory $configurationProfileFactory,
//    ) {
//        $configurationProfile = $configurationProfileFactory->getConfigurationProfile($this->getConfigurationProfileName());
//        $connection = $this->getConnection();
//        $schema = $this->getSchema($connection);
//        $table = $this->getTable();
//
//        /*
//         * TODO: allow command line options to modify state of the $configurationProfile graph
//         */
//
//        /*
//         * Create the correct analyser for the configuration profile
//         */
//        $databaseAnalyser =  $analyserFactory->databaseAnalyser($configurationProfile);
//
//        /*
//         * Allow the $databaseAnalyser to create the Database Blueprint
//         */
//        $databaseBlueprint = $databaseAnalyser->analyseDatabase(
//            $configurationProfile->getDatabaseBlueprintConfiguration()
//        );
//
//        /*
//         * Generate class files
//         */
//        $validatorGenerator = new DtoValidatorGenerator($configurationProfile);
//
//        $schemaBlueprint = $databaseBlueprint->getSchemaBlueprint($schema);
//
//
//        if (!empty($table)) {
//            // Generate only for the specified table
//            $tableBlueprint = $schemaBlueprint->getTableBlueprint($table);
//            $this->writeClassFiles(
//                $validatorGenerator,
//                $validatorGenerator->generateValidatorClass($tableBlueprint),
//                $validatorGenerator->generateValidatorAbstractClass($tableBlueprint)
//            );
//            return;
//        }
//
//        /*
//         * Display the data that would be used to perform code generation
//         */
//        foreach ($schemaBlueprint->getTableBlueprints() as $tableBlueprint) {
//            $this->writeClassFiles(
//                $validatorGenerator,
//                $validatorGenerator->generateValidatorClass($tableBlueprint),
//                $validatorGenerator->generateValidatorAbstractClass($tableBlueprint)
//            );
//        }
//    }
//
//    protected function writeClassFiles(
//        CodeFormatter $codeFormatter,
//        CodeWriter $codeWriter,
//        \Reliese\Generator\Validator\DtoValidatorGenerator $validatorGenerator,
//        ClassDefinition $modelClass,
//        ClassDefinition $abstractModelClass
//    ) {
//        $this->codeWriter->overwriteClassDefinition(
//            $validatorGenerator->getAbstractModelClassFilePath($abstractModelClass),
//            $this->classFormatter->format($abstractModelClass)
//        );
//
//        $this->codeWriter->createClassDefinition(
//            $validatorGenerator->getModelClassFilePath($modelClass),
//            $this->classFormatter->format($modelClass)
//        );
//    }
//
//    /**
//     * @return string
//     */
//    protected function getConnection(): string
//    {
//        return $this->option('connection') ?: $this->config->get('database.default');
//    }
//
//    /**
//     * @param $connection
//     *
//     * @return string
//     */
//    protected function getSchema($connection): string
//    {
//        return $this->option('schema') ?: $this->config->get("database.connections.$connection.database");
//    }
//
//    /**
//     * @return ?string
//     */
//    protected function getTable(): ?string
//    {
//        return $this->option('table');
//    }
    protected function initializeTableBasedCodeGenerators(): array
    {
        // TODO: Implement initializeTableBasedCodeGenerators() method.
        throw new \Exception(__METHOD__ . " has not been implemented.");
    }
}
