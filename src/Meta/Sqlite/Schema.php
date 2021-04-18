<?php

namespace Reliese\Meta\Sqlite;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column as DoctrineColumn;
use Illuminate\Database\SQLiteConnection;
use Reliese\Meta\Blueprint;
use Illuminate\Database\Connection;
use Reliese\Meta\Index;
use Reliese\Meta\Relation;
use Reliese\Meta\RelationBag;

/**
 * Created by Cristian.
 * Date: 18/09/16 06:50 PM.
 */
class Schema implements \Reliese\Meta\Schema
{
    /**
     * @var string
     */
    protected $schema;

    /**
     * @var SQLiteConnection
     */
    protected $connection;

    /**
     * @var bool
     */
    protected $loaded = false;

    /**
     * @var Blueprint[]
     */
    protected $tables = [];

    /**
     * Mapper constructor.
     *
     * @param string $schema
     * @param SQLiteConnection $connection
     */
    public function __construct(string $schema, $connection)
    {
        $this->schema = $schema;
        $this->connection = $connection;
        /* Sqlite has a bool type that doctrine isn't registering */
        $this->connection->getDoctrineConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('bool', 'boolean');
        $this->load();
    }

    /**
     * @return AbstractSchemaManager
     * @todo: Use Doctrine instead of raw database queries
     */
    public function manager()
    {
        return $this->connection->getDoctrineSchemaManager();
    }

    /**
     * Loads schema's tables' information from the database.
     */
    protected function load()
    {
        $tables = $this->fetchTables();

        foreach ($tables as $table) {
            $blueprint = new Blueprint($this->connection->getName(), $this->schema, $table);
            $this->fillColumns($blueprint);
            $this->fillConstraints($blueprint);
            $this->tables[$table] = $blueprint;
        }
    }

    /**
     * @return string[]
     * @internal param string $schema
     */
    protected function fetchTables()
    {
        $names = $this->manager()->listTableNames();

        return array_diff($names, [
            'sqlite_master',
            'sqlite_sequence',
            'sqlite_stat1',
        ]);
    }

    /**
     * @param Blueprint $blueprint
     *
     * @throws Exception
     */
    protected function fillColumns(Blueprint $blueprint)
    {
        $columns = $this->manager()->listTableColumns($blueprint->table());

        foreach ($columns as $column) {
            $blueprint->withColumn(
                $this->parseColumn($column)
            );
        }
    }

    /**
     * @param DoctrineColumn $metadata
     *
     * @return \Reliese\Meta\Column
     */
    protected function parseColumn(DoctrineColumn $metadata): \Reliese\Meta\Column
    {
        return (new Column($metadata))->normalize();
    }

    /**
     * @param Blueprint $blueprint
     *
     * @throws Exception
     */
    protected function fillConstraints(Blueprint $blueprint)
    {
        $this->fillPrimaryKey($blueprint);
        $this->fillIndexes($blueprint);
        $this->fillRelations($blueprint);
    }

    /**
     * @param Blueprint $blueprint
     *
     * @throws Exception
     * @todo: Support named primary keys
     */
    protected function fillPrimaryKey(Blueprint $blueprint)
    {
        $indexes = $this->manager()->listTableIndexes($blueprint->table());

        $blueprint->withPrimaryKey(new Index(
            Index::NAME_PRIMARY,
            '',
            $indexes['primary']->getColumns()
        ));
    }

    /**
     * @param Blueprint $blueprint
     *
     * @internal param string $sql
     */
    protected function fillIndexes(Blueprint $blueprint)
    {
        $indexes = $this->manager()->listTableIndexes($blueprint->table());
        unset($indexes['primary']);

        foreach ($indexes as $setup) {
            $blueprint->withIndex(new Index(
                $setup->isUnique() ? Index::NAME_UNIQUE : Index::NAME_INDEX,
                $setup->getName(),
                $setup->getColumns()
            ));
        }
    }

    /**
     * @param Blueprint $blueprint
     *
     * @throws Exception
     * @todo: Support named foreign keys
     */
    protected function fillRelations(Blueprint $blueprint)
    {
        $relations = $this->manager()->listTableForeignKeys($blueprint->table());

        foreach ($relations as $setup) {
            $table = [
                'database' => '',
                'table' => $setup->getForeignTableName()
            ];

            $blueprint->withRelation(new Relation(
                $setup->getName(),
                $setup->getColumns(),
                $setup->getForeignColumns(),
                $table,
            ));
        }
    }

    /**
     * @param \Illuminate\Database\Connection $connection
     * @deprecated use \Reliese\Meta\Sqlite\Database::getSchemaNames
     * @return array
     */
    public static function schemas(Connection $connection)
    {
        return (new Database($connection))->getSchemaNames();
    }

    /**
     * @return string
     */
    public function schema()
    {
        return $this->schema;
    }

    /**
     * @param string $table
     *
     * @return bool
     */
    public function has(string $table): bool
    {
        return array_key_exists($table, $this->tables);
    }

    /**
     * @return Blueprint[]
     */
    public function tables(): array
    {
        return $this->tables;
    }

    /**
     * @param string $table
     *
     * @return Blueprint
     */
    public function table(string $table): Blueprint
    {
        if (! $this->has($table)) {
            throw new \InvalidArgumentException("Table [$table] does not belong to schema [{$this->schema}]");
        }

        return $this->tables[$table];
    }

    /**
     * @return \Illuminate\Database\MySqlConnection
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * @param Blueprint $table
     *
     * @return array
     */
    public function referencing(Blueprint $table): array
    {
        $references = [];

        foreach ($this->tables as $blueprint) {
            foreach ($blueprint->references($table) as $reference) {
                $references[] = new RelationBag(
                    $blueprint,
                    $reference
                );
            }
        }

        return $references;
    }
}
