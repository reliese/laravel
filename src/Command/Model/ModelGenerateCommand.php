<?php

namespace Reliese\Command\Model;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Reliese\Blueprint\BlueprintFactory;
use Reliese\Coders\Console\CodeModelsCommand;
use Reliese\Coders\Model\Factory;

/**
 * Class MakeModelsCommand
 */
class MakeModelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @see Keep in sync with \Reliese\Coders\Console\CodeModelsCommand::$signature
     * @var string
     */
    protected $signature = 'reliese:model:make
                            {--s|schema= : The name of the MySQL database}
                            {--c|connection= : The name of the connection}
                            {--t|table= : The name of the table}';

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

        $databaseBlueprint = $blueprintFactory->database($connection);

        foreach ($databaseBlueprint->getSchemaBlueprints() as $schemaBlueprint) {
            $this->output->writeln(
                sprintf(
                    $schemaBlueprint->getSchemaName()." has \"%s\" tables",
                    count($schemaBlueprint->getTableBlueprints())
                )
            );
            $tableData = [];
            foreach ($schemaBlueprint->getTableBlueprints() as $tableBlueprint) {
                $tableData[] = [$tableBlueprint->getName(), \implode(', ', $tableBlueprint->getColumnNames())];
            }
            $this->output->table(
                ['table', 'columns'],
                $tableData
            );
        }
        return;
        // Check whether we just need to generate one table
        if ($table) {
            $this->models->on($connection)->create($schema, $table);
            $this->info("Check out your models for $table");
        }

        // Otherwise map the whole database
        else {
            $this->models->on($connection)->map($schema);
            $this->info("Check out your models for $schema");
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
