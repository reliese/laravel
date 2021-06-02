<?php

namespace Reliese\Command;

use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Format\CodeFormatter;
use Reliese\MetaCode\Format\IndentationProvider;
use Reliese\MetaCode\Writers\CodeWriter;
use function get_class;
/**
 * Class AbstractCodeGenerationCommand
 */
abstract class AbstractCodeGenerationCommand extends AbstractDatabaseAnalysisCommand
{
    /**
     * @return ColumnBasedCodeGeneratorInterface[]
     */
    protected abstract function initializeTableBasedCodeGenerators(): array;

    protected function processDatabaseBlueprint(DatabaseBlueprint $databaseBlueprint)
    {
        $codeGenerators = $this->initializeTableBasedCodeGenerators();

        $schemaBlueprint = $databaseBlueprint->getSchemaBlueprint($this->getSchema());

        /** @var CodeWriter $codeWriter */
        $codeWriter = app()->make(CodeWriter::class);

        /*
         * Display the data that would be used to perform code generation
         */
        foreach ($schemaBlueprint->getTableBlueprints() as $tableBlueprint) {
            if ($this->hasTableOption() && $this->getTable() !== $tableBlueprint->getName()) {
                // skip the table as it doesn't match the table filter option
                continue;
            }

            /** @var ColumnBasedCodeGeneratorInterface $codeGenerator */
            foreach ($codeGenerators as $codeGenerator) {
                $phpFileDefinition = $codeGenerator->getPhpFileDefinition($tableBlueprint);
                $codeWriter->writePhpFile($phpFileDefinition);
            }
        }
    }
}