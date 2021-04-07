<?php

namespace Reliese\Analyser\Doctrine;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Support\Facades\Log;
use Reliese\Analyser\DatabaseAnalyserInterface;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\DatabaseDescriptionDto;
use Reliese\Blueprint\ForeignKeyBlueprint;
use Reliese\Configuration\DatabaseBlueprintConfiguration;
use Reliese\Filter\SchemaFilter;
use Reliese\Filter\StringFilter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MySqlDatabaseAnalyser
 */
class DoctrineDatabaseAnalyser implements DatabaseAnalyserInterface
{
    /**
     * @var DatabaseBlueprint
     */
    private DatabaseBlueprint $databaseBlueprint;

    /**
     * @var DoctrineDatabaseAssistantInterface
     */
    private DoctrineDatabaseAssistantInterface $doctrineDatabaseAssistant;

    /**
     * @var DoctrineSchemaAnalyser[]
     */
    private array $schemaAnalysers = [];

    /**
     * MySqlDatabaseAnalyser constructor.
     *
     * @param DoctrineDatabaseAssistantInterface $doctrineDatabaseAssistant
     */
    public function __construct(DoctrineDatabaseAssistantInterface $doctrineDatabaseAssistant)
    {
        $this->doctrineDatabaseAssistant = $doctrineDatabaseAssistant;
    }

    /**
     * @param DatabaseBlueprintConfiguration $databaseBlueprintConfiguration
     *
     * @return DatabaseBlueprint
     */
    public function analyseDatabase(DatabaseBlueprintConfiguration $databaseBlueprintConfiguration): DatabaseBlueprint
    {
        $databaseBlueprint = new DatabaseBlueprint($databaseBlueprintConfiguration);
        /*
         * Create a SchemaBlueprint for each schema
         */
        $schemaNames = $this->getSchemaNames();
        foreach ($schemaNames as $schemaName) {
            /*
             * Check to see if the current schema should be analyzed
             *
             * Must be specifically included and NOT match an exclude filter
             */
            if ($databaseBlueprintConfiguration->getSchemaFilter()->isExcludedSchema($schemaName)) {
                Log::debug("Skipping Schema \"$schemaName\"");
                continue;
            }

            $schemaAnalyser = $this->getSchemaAnalyser($databaseBlueprint, $schemaName);
            $databaseBlueprint->addSchemaBlueprint(
                // TODO: Add support for Views
                $schemaAnalyser->analyseSchemaObjectStructures($databaseBlueprint)
            );
        }

        /*
         * Analyse foreign key constraint relationships which could potentially span schemas
         */
        foreach ($schemaNames as $schemaName) {
            $schemaAnalyser = $this->getSchemaAnalyser($databaseBlueprint, $schemaName);

            foreach ($schemaAnalyser->getTableDefinitions() as $tableName => $doctrineTableDefinition) {

                $foreignKeys = $doctrineTableDefinition->getForeignKeys();
                if (empty($foreignKeys)) {
                    continue;
                }

                /*
                 * Find the blueprint for the table
                 */
                $tableBlueprint = $databaseBlueprint->getSchemaBlueprint($schemaName)->getTableBlueprint($tableName);

                Log::info('Looking for FKeys in columns: '.\implode(', ', $tableBlueprint->getColumnNames()));

                foreach ($foreignKeys as $foreignKey) {
                    /*
                     * Get the referencing column blueprints
                     */
                    $referencingColumnBlueprints = [];
                    foreach ($foreignKey->getLocalColumns() as $referencingColumnName) {
                        $referencingColumnBlueprints[$referencingColumnName] = $tableBlueprint->getColumnBlueprint($referencingColumnName);
                    }

                    /*
                     * Get the referenced table blueprint
                     *
                     * TODO: SUPPORT CROSS SCHEMA FOREIGN KEYS as the referenced table does not have to be in the same schema
                     * $referencedTableSchemaName = $schemaName; should be $foreignKey->getForeignSchemaName() but this
                     * is not supported by Doctrine's ForeignKey object
                     */
                    $referencedTableSchemaName = $schemaName;
                    $referencedTableName = $foreignKey->getForeignTableName();

                    $referencedSchemaBlueprint = $databaseBlueprint->getSchemaBlueprint($referencedTableSchemaName);

                    if (!$referencedSchemaBlueprint->hasTableBlueprint($referencedTableName)) {
                        /*
                         * TODO: Decide if we should apply a "best guess" methodology where table and column matching resolves the key
                         * TODO: Alternative, throw an exception that is handled by requiring the user to specify the schema and for this FK manually and store that in a config
                         */
                        Log::notice(sprintf("Skipping FK \"%s\": Unable to resolve FK relationships across schemas", $foreignKey->getName()));
                        continue;
                    }

                    $referencedTableBlueprint = $referencedSchemaBlueprint->getTableBlueprint($referencedTableName);

                    /*
                     * Get the referenced column blueprints
                     */
                    $referencedColumns = [];
                    foreach ($foreignKey->getForeignColumns() as $referencedColumnName) {
                        $referencedColumns[$referencedColumnName] = $referencedTableBlueprint->getColumnBlueprint($referencedColumnName);
                    }

                    /*
                     * Create the FK blueprint that contains pointers to the other blueprints
                     */
                    $foreignKeyBlueprint =  new ForeignKeyBlueprint(
                        $tableBlueprint,
                        $foreignKey->getName(),
                        $referencingColumnBlueprints,
                        $referencedTableBlueprint,
                        $referencedColumns
                    );

                    $tableBlueprint->addForeignKeyBlueprint($foreignKeyBlueprint);
                }
            }
        }

        return $databaseBlueprint;
    }

    /**
     * @inheritDoc
     */
    protected function getSchemaNames(): array
    {
        return $this->doctrineDatabaseAssistant->getDoctrineSchemaManager()->listDatabases();
    }

    /**
     * @return DatabaseBlueprint
     */
    protected function getDatabaseBlueprint() : DatabaseBlueprint
    {
        return $this->databaseBlueprint;
    }

    /**
     * @param DatabaseBlueprint $databaseBlueprint
     * @param string $schemaName
     *
     * @return DoctrineSchemaAnalyser
     */
    protected function getSchemaAnalyser(DatabaseBlueprint $databaseBlueprint, string $schemaName): DoctrineSchemaAnalyser
    {
        if (\array_key_exists($schemaName, $this->schemaAnalysers)) {
            return $this->schemaAnalysers[$schemaName];
        }

        $schemaSpecificConnection = $this->doctrineDatabaseAssistant->getConnection($schemaName);
        return $this->schemaAnalysers[$schemaName] = $this->schemaAnalysers[$schemaName] =  new DoctrineSchemaAnalyser(
            $schemaName,
            $databaseBlueprint,
            $this,
            $schemaSpecificConnection,
            $schemaSpecificConnection->getDoctrineSchemaManager()
        );
    }
}
