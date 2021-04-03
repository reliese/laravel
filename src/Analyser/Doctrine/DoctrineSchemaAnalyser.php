<?php

namespace Reliese\Analyser\Doctrine;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\UniqueConstraint;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Log;
use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\IndexBlueprint;
use Reliese\Blueprint\SchemaBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Blueprint\UniqueKeyBlueprint;

/**
 * Class MySqlSchemaAnalyser
 */
class DoctrineSchemaAnalyser
{
    /**
     * @var DatabaseBlueprint
     */
    private DatabaseBlueprint $databaseBlueprint;

    /**
     * @var DoctrineDatabaseAnalyser
     */
    private DoctrineDatabaseAnalyser $doctrineDatabaseAnalyser;

    /**
     * @var AbstractSchemaManager
     */
    private AbstractSchemaManager $doctrineSchemaManager;

    /**
     * @var string
     */
    private string $schemaName;

    /**
     * @var ConnectionInterface
     */
    private ConnectionInterface $schemaSpecificConnection;

    /**
     * @var Table[]
     */
    private array $tableDefinitions = [];

    /**
     * MySqlSchemaAnalyser constructor.
     *
     * @param string $schemaName
     * @param DatabaseBlueprint $databaseBlueprint
     * @param DoctrineDatabaseAnalyser $doctrineDatabaseAnalyser
     * @param ConnectionInterface $connection
     * @param AbstractSchemaManager $doctrineSchemaManager
     */
    public function __construct(string $schemaName,
        DatabaseBlueprint $databaseBlueprint,
        DoctrineDatabaseAnalyser $doctrineDatabaseAnalyser,
        ConnectionInterface $connection,
        AbstractSchemaManager $doctrineSchemaManager)
    {
        $this->doctrineDatabaseAnalyser = $doctrineDatabaseAnalyser;
        $this->schemaSpecificConnection = $connection;
        $this->schemaName = $schemaName;
        $this->doctrineSchemaManager = $doctrineSchemaManager;
        $this->databaseBlueprint = $databaseBlueprint;
    }

    /**
     * @var SchemaBlueprint|null
     */
    private ?SchemaBlueprint $schemaBlueprint = null;

    /**
     * @return SchemaBlueprint
     */
    public function getSchemaBlueprint(): SchemaBlueprint
    {
        if (null !== $this->schemaBlueprint) {
            return $this->schemaBlueprint;
        }

        return $this->schemaBlueprint = new SchemaBlueprint($this->getDatabaseBlueprint(), $this->getSchemaName());
    }

    /**
     * @param DatabaseBlueprint $databaseBlueprint
     *
     * @return SchemaBlueprint
     */
    public function analyseSchemaObjectStructures(DatabaseBlueprint $databaseBlueprint): SchemaBlueprint
    {
        Log::info("Creating SchemaBlueprint for \"{$this->getSchemaName()}\"");

        $schemaBlueprint = $this->getSchemaBlueprint();

        /**
         * For each table...
         */
        $tableDefinitions = $this->getDoctrineSchemaManager()->listTables();
        if (!empty($tableDefinitions)) {
            foreach ($tableDefinitions as $tableDefinition) {
                /*
                 * Keep for future use
                 */
                $this->addTableDefinition($tableDefinition);

                $tableBlueprint = $this->analyseTable($schemaBlueprint, $tableDefinition);

                $schemaBlueprint->addTableBlueprint($tableBlueprint);
            }
        }

        /*
         * Relationships cannot be analysed until after all objects have been loaded
         */
        return $schemaBlueprint;
    }

    /**
     * @return Table[]
     */
    public function getTableDefinitions(): array
    {
        return $this->tableDefinitions;
    }

    /**
     * @return AbstractSchemaManager
     */
    protected function getDoctrineSchemaManager(): AbstractSchemaManager
    {
        return $this->doctrineSchemaManager;
    }

    /**
     * @param Table $tableDefinition
     *
     * @return $this
     */
    private function addTableDefinition(Table $tableDefinition): static
    {
        $this->tableDefinitions[$tableDefinition->getName()] = $tableDefinition;
        return $this;
    }

    /**
     * @param Column $columnDefinition
     * @param TableBlueprint $tableBlueprint
     */
    private function analyseColumn(ColumnOwnerInterface $columnOwner,
        Column $columnDefinition): ColumnBlueprint
    {
        $fullyQualifiedName = $columnDefinition->getFullQualifiedName('default');

        $isNullable = !$columnDefinition->getNotnull();
        $hasDefault = null === $columnDefinition->getDefault();

        $columnBlueprint = new ColumnBlueprint($columnOwner,
            $columnDefinition->getName(),
            $columnDefinition->getType()->getName(),
            $isNullable,
            $columnDefinition->getLength() ?? -1,
            $columnDefinition->getPrecision() ?? -1,
            $columnDefinition->getScale() ?? -1,
            $columnDefinition->getAutoincrement(),
            $hasDefault);

        return $columnBlueprint;
    }

    /**
     * @param TableBlueprint $tableBlueprint
     * @param Index $indexDefinition
     *
     * @return IndexBlueprint
     */
    private function analyseIndex(TableBlueprint $tableBlueprint, Index $indexDefinition): IndexBlueprint
    {
        return new IndexBlueprint($tableBlueprint, $indexDefinition->getName(), $indexDefinition->getColumns());
    }

    /**
     * @param SchemaBlueprint $schemaBlueprint
     * @param Table $tableDefinition
     *
     * @return TableBlueprint
     */
    private function analyseTable(SchemaBlueprint $schemaBlueprint, Table $tableDefinition): TableBlueprint
    {
        Log::info(sprintf("Creating TableBlueprint for \"%s\"", $tableDefinition->getName()));
        $tableName = $tableDefinition->getName();
        /*
         * Keep the table definition for use while resolving relationships
         */

        $tableBlueprint = new TableBlueprint($schemaBlueprint, $tableName);
        $this->analyseTableColumns($tableDefinition, $tableBlueprint);
        $this->analyseTableIndexes($tableDefinition, $tableBlueprint);
        $this->analyseTableUniqueConstraints($tableDefinition, $tableBlueprint);

        return $tableBlueprint;
    }

    /**
     * @param Table $tableDefinition
     * @param TableBlueprint $tableBlueprint
     */
    private function analyseTableColumns(Table $tableDefinition, TableBlueprint $tableBlueprint): void
    {
        $columnDefinitions = $tableDefinition->getColumns();
        if (empty($columnDefinitions)) {
            return;
        }

        foreach ($columnDefinitions as $columnDefinition) {
            $columnBlueprint = $this->analyseColumn($tableBlueprint, $columnDefinition);
            $tableBlueprint->addColumnBlueprint($columnBlueprint);
        }
    }

    /**
     * @param Table $tableDefinition
     * @param TableBlueprint $tableBlueprint
     */
    private function analyseTableIndexes(Table $tableDefinition, TableBlueprint $tableBlueprint): void
    {
        $indexDefinitions = $tableDefinition->getIndexes();
        if (empty($indexDefinitions)) {
            return;
        }

        foreach ($indexDefinitions as $indexDefinition) {
            $indexBlueprint = $this->analyseIndex($tableBlueprint, $indexDefinition);
            $tableBlueprint->addIndexBlueprint($indexBlueprint);
        }
    }

    /**
     * @param ColumnOwnerInterface $columnOwner
     * @param UniqueConstraint $uniqueConstraint
     *
     * @return UniqueKeyBlueprint
     */
    private function analyseTableUniqueConstraint(ColumnOwnerInterface $columnOwner,
        UniqueConstraint $uniqueConstraint): UniqueKeyBlueprint
    {
        return new UniqueKeyBlueprint($columnOwner, $uniqueConstraint->getName(), $uniqueConstraint->getColumns());
    }

    /**
     * @param Table $tableDefinition
     * @param TableBlueprint $tableBlueprint
     */
    private function analyseTableUniqueConstraints(Table $tableDefinition, TableBlueprint $tableBlueprint): void
    {
        $uniqueKeyDefinitions = $tableDefinition->getUniqueConstraints();
        if (empty($uniqueKeyDefinitions)) {
            return;
        }

        foreach ($uniqueKeyDefinitions as $uniqueKeyDefinition) {
            $uniqueKeyBlueprint = $this->analyseTableUniqueConstraint($tableBlueprint, $uniqueKeyDefinition);
            $tableBlueprint->addUniqueConstraintBlueprint($uniqueKeyBlueprint);
        }
    }

    /**
     * @return string
     */
    private function getSchemaName(): string
    {
        return $this->schemaName;
    }

    /**
     * @return DatabaseBlueprint
     */
    protected function getDatabaseBlueprint(): DatabaseBlueprint
    {
        return $this->databaseBlueprint;
    }
}
