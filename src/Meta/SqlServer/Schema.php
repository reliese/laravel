<?php

namespace Reliese\Meta\SqlServer;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Reliese\Meta\Blueprint;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;

/**
 * SQLServer Schema Metadata Handling
 * Adapted from PostgreSQL Schema implementation
 * Date: 2024-12-07
 */
class Schema implements \Reliese\Meta\Schema
{
    /**
     * @var string
     */
    protected $schema;

    /**
     * @var \Illuminate\Database\SQLServerConnection
     */
    protected $connection;

    /**
     * @var bool
     */
    protected $loaded = false;

    /**
     * @var \Reliese\Meta\Blueprint[]
     */
    protected $tables = [];

    /**
     * @var string
     */
    protected $schema_database;

    /**
     * Schema constructor.
     *
     * @param string $schema
     * @param \Illuminate\Database\SQLServerConnection $connection
     */
    public function __construct($schema, $connection)
    {
        $this->schema_database = Config::get("database.connections.sqlsrv.schema", 'dbo');
        $this->schema = $schema;
        $this->connection = $connection;

        $this->load();
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
        $this->loaded = true;
    }

    /**
     * Fetch tables for the current schema
     *
     * @return array
     */
    protected function fetchTables()
    {
        $rows = $this->arraify($this->connection->select(
            "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES " .
                "WHERE TABLE_SCHEMA = '$this->schema_database' AND TABLE_TYPE = 'BASE TABLE'"
        ));
        return array_column($rows, 'TABLE_NAME');
    }

    /**
     * Fill columns for a given blueprint
     *
     * @param \Reliese\Meta\Blueprint $blueprint
     */
    protected function fillColumns(Blueprint $blueprint)
    {
        $rows = $this->arraify($this->connection->select(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS " .
                "WHERE TABLE_SCHEMA = '$this->schema_database' " .
                "AND TABLE_NAME = " . $this->wrap($blueprint->table())
        ));

        foreach ($rows as $column) {
            $blueprint->withColumn(
                $this->parseColumn($column)
            );
        }
    }

    /**
     * Parse column metadata
     *
     * @param array $metadata
     * @return \Illuminate\Support\Fluent
     */
    protected function parseColumn($metadata)
    {
        return (new Column($metadata))->normalize();
    }

    /**
     * Fill constraints for a given blueprint
     *
     * @param \Reliese\Meta\Blueprint $blueprint
     */
    protected function fillConstraints(Blueprint $blueprint)
    {
        $relations = $this->fetchTableRelations($blueprint->table());
        $this->fillPrimaryKey($relations, $blueprint);
        $this->fillRelations($relations, $blueprint);
        $this->fillIndexes($blueprint);
    }

    /**
     * Fetch table relations
     *
     * @param string $tableName
     * @return array
     */
    protected function fetchTableRelations($tableName)
    {
        $sql = "
        SELECT 
            COL_NAME(fk.parent_object_id, fk.parent_column_id) AS column_name,
            OBJECT_NAME(fk.referenced_object_id) AS referenced_table,
            COL_NAME(fk.referenced_object_id, fk.referenced_column_id) AS referenced_column,
            fk.name AS constraint_name,
            CASE 
                WHEN fk.is_primary_key = 1 THEN 'p'
                WHEN fk.is_unique_constraint = 1 THEN 'u'
                ELSE 'f'
            END AS constraint_type
        FROM sys.foreign_keys fk
        INNER JOIN sys.tables t ON t.object_id = fk.parent_object_id
        WHERE t.name = '$tableName' AND SCHEMA_NAME(t.schema_id) = '$this->schema_database'
        ";

        return $this->arraify($this->connection->select($sql));
    }

    /**
     * Fill primary key for blueprint
     *
     * @param array $relations
     * @param \Reliese\Meta\Blueprint $blueprint
     */
    protected function fillPrimaryKey($relations, Blueprint $blueprint)
    {
        $pk = [];
        foreach ($relations as $row) {
            if ($row['constraint_type'] === 'p') {
                $pk[] = $row['column_name'];
            }
        }

        if (!empty($pk)) {
            $key = [
                'name' => 'primary',
                'index' => '',
                'columns' => $pk,
            ];

            $blueprint->withPrimaryKey(new Fluent($key));
        }
    }

    /**
     * Fill indexes for blueprint
     *
     * @param \Reliese\Meta\Blueprint $blueprint
     */
    protected function fillIndexes(Blueprint $blueprint)
    {
        $indexSql = "
        SELECT 
            i.name AS index_name,
            COL_NAME(ic.object_id, ic.column_id) AS column_name,
            i.is_unique,
            i.is_primary_key
        FROM sys.indexes i
        INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
        INNER JOIN sys.tables t ON t.object_id = i.object_id
        WHERE t.name = '{$blueprint->table()}' 
        AND SCHEMA_NAME(t.schema_id) = '{$this->schema_database}'
        AND i.is_primary_key = 0
        ";

        $indexes = $this->arraify($this->connection->select($indexSql));

        $processedIndexes = [];
        foreach ($indexes as $index) {
            $indexName = $index['index_name'];

            if (!isset($processedIndexes[$indexName])) {
                $processedIndexes[$indexName] = [
                    'name' => $index['is_unique'] ? 'unique' : 'index',
                    'columns' => [$index['column_name']],
                    'index' => $indexName,
                ];
            } else {
                $processedIndexes[$indexName]['columns'][] = $index['column_name'];
            }
        }

        foreach ($processedIndexes as $indexData) {
            $blueprint->withIndex(new Fluent($indexData));
        }
    }

    /**
     * Fill relations for blueprint
     *
     * @param array $relations
     * @param \Reliese\Meta\Blueprint $blueprint
     */
    protected function fillRelations($relations, Blueprint $blueprint)
    {
        $fk = [];
        foreach ($relations as $row) {
            if ($row['constraint_type'] === 'f') {
                $relName = $row['constraint_name'];
                if (!array_key_exists($relName, $fk)) {
                    $fk[$relName] = [
                        'columns' => [],
                        'ref' => [],
                    ];
                }
                $fk[$relName]['columns'][] = $row['column_name'];
                $fk[$relName]['ref'][] = $row['referenced_column'];
                $fk[$relName]['table'] = $row['referenced_table'];
            }
        }

        foreach ($fk as $row) {
            $relation = [
                'name' => 'foreign',
                'index' => '',
                'columns' => $row['columns'],
                'references' => $row['ref'],
                'on' => [$this->schema, $row['table']],
            ];

            $blueprint->withRelation(new Fluent($relation));
        }
    }

    /**
     * Quick conversion of database results to array
     *
     * @param $data
     * @return mixed
     */
    protected function arraify($data)
    {
        return json_decode(json_encode($data), true);
    }

    /**
     * Wrap values for SQL queries
     *
     * @param string $table
     * @return string
     */
    protected function wrap($table)
    {
        $pieces = explode('.', str_replace('\'', '', $table));
        return implode('.', array_map(function ($piece) {
            return "'$piece'";
        }, $pieces));
    }

    /**
     * Get available schemas/databases
     *
     * @param \Illuminate\Database\Connection $connection
     * @return array
     */
    public static function schemas(Connection $connection)
    {
        $schemas = $connection->select('SELECT name FROM sys.databases');
        $schemas = array_column($schemas, 'name');

        return array_diff($schemas, [
            'master',
            'tempdb',
            'model',
            'msdb'
        ]);
    }

    /**
     * Get current schema
     *
     * @return string
     */
    public function schema()
    {
        return $this->schema;
    }

    /**
     * Check if table exists in schema
     *
     * @param string $table
     * @return bool
     */
    public function has($table)
    {
        return array_key_exists($table, $this->tables);
    }

    /**
     * Get all tables
     *
     * @return \Reliese\Meta\Blueprint[]
     */
    public function tables()
    {
        return $this->tables;
    }

    /**
     * Get specific table
     *
     * @param string $table
     * @return \Reliese\Meta\Blueprint
     * @throws \InvalidArgumentException
     */
    public function table($table)
    {
        if (!$this->has($table)) {
            throw new \InvalidArgumentException("Table [$table] does not belong to schema [{$this->schema}]");
        }

        return $this->tables[$table];
    }

    /**
     * Get connection
     *
     * @return \Illuminate\Database\Connection
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * Find tables referencing a given table
     *
     * @param \Reliese\Meta\Blueprint $table
     * @return array
     */
    public function referencing(Blueprint $table)
    {
        $references = [];

        foreach ($this->tables as $blueprint) {
            foreach ($blueprint->references($table) as $reference) {
                $references[] = [
                    'blueprint' => $blueprint,
                    'reference' => $reference,
                ];
            }
        }

        return $references;
    }
}
