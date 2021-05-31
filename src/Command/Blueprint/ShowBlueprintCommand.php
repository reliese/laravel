<?php

namespace Reliese\Command\Blueprint;

use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Command\AbstractDatabaseAnalysisCommand;
use function count;
use function sprintf;

/**
 * Class ShowBlueprintCommand
 */
class ShowBlueprintCommand extends AbstractDatabaseAnalysisCommand
{

    protected function getCommandName(): string
    {
        return 'reliese:blueprint:show';
    }

    protected function getCommandDescription(): string
    {
        return 'Analyses the database and dumps the resulting blueprint to stdout.';
    }

    protected function processDatabaseBlueprint(DatabaseBlueprint $databaseBlueprint)
    {
        /*
         * Display the data that would be used to perform code generation
         */
        foreach ($databaseBlueprint->getSchemaBlueprints() as $schemaBlueprint) {

            if ($this->hasSchemaOption() && $this->getSchema() !== $schemaBlueprint->getSchemaName()) {
                // skip the schema because it doesn't match the provided option
                // echo "skipping schema ".$schemaBlueprint->getSchemaName()."\n";
                continue;
            }

            $this->output->writeln(
                sprintf(
                    $schemaBlueprint->getSchemaName()." has \"%s\" tables",
                    count($schemaBlueprint->getTableBlueprints())
                )
            );
            $tableData = [];
            foreach ($schemaBlueprint->getTableBlueprints() as $tableBlueprint) {

                if ($this->hasTableOption() && $this->getTable() !== $tableBlueprint->getName()) {
                    // skip this table because it doesn't match the table option
                    // echo "skipping table because ".$this->getTable()." !== ".$tableBlueprint->getName()."\n";
                    continue;
                }

                $tableData[] = [
                    $tableBlueprint->getUniqueName(),
                    \implode(', ', $tableBlueprint->getColumnNames()),
                    \implode(', ', $tableBlueprint->getForeignKeyNames())
                ];
            }
            $this->output->table(
                ['table', 'columns', 'keys'],
                $tableData
            );
        }
    }
}
