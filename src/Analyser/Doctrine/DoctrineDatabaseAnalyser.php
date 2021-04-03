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
use Reliese\Filter\StringFilter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MySqlDatabaseAnalyser
 */
class DoctrineDatabaseAnalyser implements DatabaseAnalyserInterface
{
    const COMPATIBILITY_TYPE_NAME = 'MySql';

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var DatabaseBlueprint
     */
    private DatabaseBlueprint $databaseBlueprint;

    /**
     * @var callable
     */
    private $doctrineSchemaManagerConfigurationDelegate;

    /**
     * @var string[]
     */
    private array $excludeSchemas = [];

    /**
     * @var DoctrineSchemaAnalyser[]
     */
    private $schemaAnalysers = [];

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ConnectionInterface[]
     */
    private $schemaConnections;

    /**
     * @var StringFilter
     */
    private StringFilter $schemaFilter;

    /**
     * MySqlDatabaseAnalyser constructor.
     *
     * @param StringFilter $schemaFilter
     * @param callable $doctrineSchemaManagerConfigurationDelegate
     * @param ConnectionFactory $connectionFactory
     * @param ConnectionInterface $connection
     * @param OutputInterface $output
     */
    public function __construct(
        StringFilter $schemaFilter,
        callable $doctrineSchemaManagerConfigurationDelegate,
        ConnectionFactory $connectionFactory,
        ConnectionInterface $connection,
        OutputInterface $output
    ) {
        $this->connectionFactory = $connectionFactory;
        $this->doctrineSchemaManagerConfigurationDelegate = $doctrineSchemaManagerConfigurationDelegate;
        $this->connection = $connection;
        $this->output = $output;
        $this->schemaFilter = $schemaFilter;

        $databaseDescriptionDto = new DatabaseDescriptionDto($this->getCompatibilityTypeName(),
            $this->getDatabaseName(),
            $this->getReleaseVersion());

        $this->databaseBlueprint = new DatabaseBlueprint($databaseDescriptionDto);
    }

    /**
     * @return string
     */
    public function getCompatibilityTypeName(): string
    {
        return static::COMPATIBILITY_TYPE_NAME;
    }

    /**
     * Returns connections configured for the specified schema
     *
     * @param string|null $schemaName
     *
     * @return ConnectionInterface
     */
    public function getConnection(?string $schemaName = null): ConnectionInterface
    {
        if (empty($schemaName)) {
            return $this->connection;
        }

        if (!empty($this->schemaConnections[$schemaName])) {
            return $this->schemaConnections[$schemaName];
        }

        $config = $this->connection->getConfig();
        $config["database"] = $schemaName;

        return $this->schemaConnections[$schemaName] = $this->connectionFactory->make($config);
    }

    /**
     * @return DatabaseBlueprint
     * @throws \Doctrine\DBAL\Exception
     */
    public function analyseDatabase(): DatabaseBlueprint
    {
        $databaseBlueprint = $this->getDatabaseBlueprint();
        /*
         * Create a SchemaBlueprint for each schema
         */
        $schemaNames = $this->getSchemaNames();
        foreach ($schemaNames as $schemaName) {
            /*
             * Check to see if the current schema should be analyzed
             */
            if ($this->schemaFilter->isExcluded($schemaName)) {
                Log::debug("Skipping Schema \"$schemaName\"");
                continue;
            }

            $schemaAnalyser = $this->getSchemaAnalyser($schemaName);
            $databaseBlueprint->addSchemaBlueprint(
                // TODO: Add support for Views
                $schemaAnalyser->analyseSchemaObjectStructures($databaseBlueprint)
            );
        }

        /*
         * Analyse foreign key constraint relationships which could potentially span schemas
         */
        foreach ($schemaNames as $schemaName) {
            $schemaAnalyser = $this->getSchemaAnalyser($schemaName);

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
    protected function getDatabaseName(): string
    {
        return $this->getConnection()->getDoctrineConnection()->getDatabase();
    }

    /**
     * @inheritDoc
     */
    protected function getReleaseVersion(): string
    {
        $rows = $this->connection->select("SHOW VARIABLES LIKE 'version'");
        return $rows[0]->Value;
    }

    /**
     * @inheritDoc
     */
    protected function getSchemaNames(): array
    {
        return $this->getDoctrineSchemaManager()->listDatabases();
    }

    /**
     * @param AbstractSchemaManager $abstractSchemaManager
     *
     * @return AbstractSchemaManager
     */
    public function configureDoctrineSchemaManager(AbstractSchemaManager $abstractSchemaManager) : AbstractSchemaManager
    {
        return ($this->doctrineSchemaManagerConfigurationDelegate)($abstractSchemaManager);
    }

    /**
     * @return DatabaseBlueprint
     */
    protected function getDatabaseBlueprint() : DatabaseBlueprint
    {
        return $this->databaseBlueprint;
    }

    /**
     * @param string|null $schemaName
     *
     * @return AbstractSchemaManager
     * @throws \Doctrine\DBAL\Exception
     */
    private function getDoctrineSchemaManager(?string $schemaName = null) : AbstractSchemaManager
    {
        return $this->configureDoctrineSchemaManager($this->getConnection($schemaName)->getDoctrineSchemaManager());
    }

    /**
     * @param string $schemaName
     *
     * @return DoctrineSchemaAnalyser
     */
    protected function getSchemaAnalyser(string $schemaName): DoctrineSchemaAnalyser
    {
        if (\array_key_exists($schemaName, $this->schemaAnalysers)) {
            return $this->schemaAnalysers[$schemaName];
        }

        $schemaSpecificConnection = $this->getConnection($schemaName);
        return $this->schemaAnalysers[$schemaName] = $this->schemaAnalysers[$schemaName] =  new DoctrineSchemaAnalyser(
            $schemaName,
            $this->getDatabaseBlueprint(),
            $this,
            $schemaSpecificConnection,
            $this->configureDoctrineSchemaManager($schemaSpecificConnection->getDoctrineSchemaManager())
        );

    }
}
