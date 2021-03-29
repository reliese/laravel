<?php

namespace Reliese\Meta\MySql;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\MySqlConnection;
use Illuminate\Support\Arr;
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
     * @var MySqlConnection
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
     * @param MySqlConnection $connection
     */
    public function __construct($schema, $connection)
    {
        $this->schema = $schema;
        $this->connection = $connection;

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
        $tables = $this->fetchTables($this->schema);
        foreach ($tables as $table) {
            $this->loadTable($table);
        }
        $views = $this->fetchViews($this->schema);
        foreach ($views as $table) {
            $this->loadTable($table, true);
        }
    }

    /**
     * @param string $schema
     *
     * @return array
     */
    protected function fetchTables($schema)
    {
        $rows = $this->arraify($this->connection->select('SHOW FULL TABLES FROM '.$this->wrap($schema).' WHERE Table_type="BASE TABLE"'));
        $names = array_column($rows, 'Tables_in_'.$schema);

        return Arr::flatten($names);
    }

    /**
     * @param string $schema
     *
     * @return array
     */
    protected function fetchViews($schema)
    {
        $rows = $this->arraify($this->connection->select('SHOW FULL TABLES FROM '.$this->wrap($schema).' WHERE Table_type="VIEW"'));
        $names = array_column($rows, 'Tables_in_'.$schema);

        return Arr::flatten($names);
    }

    /**
     * @param Blueprint $blueprint
     */
    protected function fillColumns(Blueprint $blueprint)
    {
        $rows = $this->arraify($this->connection->select('SHOW FULL COLUMNS FROM '.$this->wrap($blueprint->qualifiedTable())));
        foreach ($rows as $column) {
            $blueprint->withColumn(
                $this->parseColumn($column)
            );
        }
    }

    /**
     * @param array $metadata
     *
     * @return \Reliese\Meta\Column
     */
    protected function parseColumn($metadata): \Reliese\Meta\Column
    {
        return (new Column($metadata))->normalize();
    }

    /**
     * @param Blueprint $blueprint
     */
    protected function fillConstraints(Blueprint $blueprint)
    {
        $row = $this->arraify($this->connection->select('SHOW CREATE TABLE '.$this->wrap($blueprint->qualifiedTable())));
        $row = array_change_key_case($row[0]);
        $sql = ($blueprint->isView() ? $row['create view'] : $row['create table']);
        $sql = str_replace('`', '', $sql);

        $this->fillPrimaryKey($sql, $blueprint);
        $this->fillIndexes($sql, $blueprint);
        $this->fillRelations($sql, $blueprint);
    }

    /**
     * Quick little hack since it is no longer possible to set PDO's fetch mode
     * to PDO::FETCH_ASSOC.
     *
     * @param $data
     * @return mixed
     */
    protected function arraify($data)
    {
        return json_decode(json_encode($data), true);
    }

    /**
     * @param string    $sql
     * @param Blueprint $blueprint
     *
     * @todo: Support named primary keys
     */
    protected function fillPrimaryKey($sql, Blueprint $blueprint)
    {
        $pattern = '/\s*(PRIMARY KEY)\s+\(([^\)]+)\)/mi';
        if (preg_match_all($pattern, $sql, $indexes, PREG_SET_ORDER) == false) {
            return;
        }

        $blueprint->withPrimaryKey(new Index(
            'primary',
            '',
            $this->columnize($indexes[0][2])
        ));
    }

    /**
     * @param string    $sql
     * @param Blueprint $blueprint
     */
    protected function fillIndexes($sql, Blueprint $blueprint)
    {
        $pattern = '/\s*(UNIQUE)?\s*(KEY|INDEX)\s+(\w+)\s+\(([^\)]+)\)/mi';
        if (preg_match_all($pattern, $sql, $indexes, PREG_SET_ORDER) == false) {
            return;
        }

        foreach ($indexes as $setup) {
            $blueprint->withIndex(new Index(
                strcasecmp($setup[1], 'unique') === 0 ? 'unique' : 'index',
                $setup[3],
                $this->columnize($setup[4])
            ));
        }
    }

    /**
     * @param string    $sql
     * @param Blueprint $blueprint
     *
     * @todo: Support named foreign keys
     */
    protected function fillRelations($sql, Blueprint $blueprint)
    {
        $pattern = '/FOREIGN KEY\s+\(([^\)]+)\)\s+REFERENCES\s+([^\(^\s]+)\s*\(([^\)]+)\)/mi';
        preg_match_all($pattern, $sql, $relations, PREG_SET_ORDER);

        foreach ($relations as $setup) {
            $table = $this->resolveForeignTable($setup[2], $blueprint);

            $blueprint->withRelation(new Relation(
                'foreign',
                '',
                $this->columnize($setup[1]),
                $this->columnize($setup[3]),
                $table,
            ));
        }
    }

    /**
     * @param string $columns
     *
     * @return array
     */
    protected function columnize($columns)
    {
        return array_map('trim', explode(',', $columns));
    }

    /**
     * Wrap within backticks.
     *
     * @param string $table
     *
     * @return string
     */
    protected function wrap($table)
    {
        $pieces = explode('.', str_replace('`', '', $table));

        return implode('.', array_map(function ($piece) {
            return "`$piece`";
        }, $pieces));
    }

    /**
     * @param string $table
     * @param Blueprint $blueprint
     *
     * @return array
     */
    protected function resolveForeignTable($table, Blueprint $blueprint)
    {
        $referenced = explode('.', $table);

        if (count($referenced) == 2) {
            return [
                'database' => current($referenced),
                'table' => next($referenced),
            ];
        }

        return [
            'database' => $blueprint->schema(),
            'table' => current($referenced),
        ];
    }

    /**
     * @param \Illuminate\Database\Connection $connection
     *
     * @return array
     */
    public static function schemas(Connection $connection)
    {
        $schemas = $connection->getDoctrineSchemaManager()->listDatabases();

        return array_diff($schemas, [
            'information_schema',
            'sys',
            'mysql',
            'performance_schema',
        ]);
    }

    /**
     * @return string
     */
    public function schema(): string
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
     * @return MySqlConnection
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * @param Blueprint $table
     *
     * @return RelationBag[]
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

    /**
     * @param string $table
     * @param bool $isView
     */
    protected function loadTable($table, $isView = false)
    {
        $blueprint = new Blueprint($this->connection->getName(), $this->schema, $table, $isView);
        $this->fillColumns($blueprint);
        $this->fillConstraints($blueprint);
        $this->tables[$table] = $blueprint;
    }
}
