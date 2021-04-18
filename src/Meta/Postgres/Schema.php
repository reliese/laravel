<?php

namespace Reliese\Meta\Postgres;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\PostgresConnection;
use Illuminate\Support\Arr;
use Reliese\Meta\Blueprint;
use Illuminate\Database\Connection;
use Reliese\Meta\Index;
use Reliese\Meta\Relation;
use Reliese\Meta\RelationBag;

/**
 * Created by rwdim from cristians MySql original.
 * Date: 25/08/18 04:13 PM.
 */
class Schema implements \Reliese\Meta\Schema
{
    /**
     * @var string
     */
    protected $schema;

    /**
     * @var PostgresConnection
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
     * @param PostgresConnection $connection
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
        // Note that "schema" refers to the database name,
        // not a pgsql schema.
        $this->connection->raw('\c '.$this->wrap($this->schema));
        $tables = $this->fetchTables($this->schema);
        foreach ($tables as $table) {
            $blueprint = new Blueprint($this->connection->getName(), $this->schema, $table);
            $this->fillColumns($blueprint);
            $this->fillConstraints($blueprint);
            $this->tables[$table] = $blueprint;
        }
        $this->loaded = true;
    }

    /**
     * @param string $schema
     *
     * @return array
     */
    protected function fetchTables($schema)
    {
        $rows = $this->arraify($this->connection->select(
            'SELECT * FROM pg_tables where schemaname=\'public\''
        ));
        $names = array_column($rows, 'tablename');

        return Arr::flatten($names);
    }

    /**
     * @param Blueprint $blueprint
     */
    protected function fillColumns(Blueprint $blueprint)
    {
        $rows = $this->arraify($this->connection->select(
            'SELECT * FROM information_schema.columns '.
            'WHERE table_schema=\'public\''.
            'AND table_name='.$this->wrap($blueprint->table())
        ));
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
        $sql = '
        SELECT child.attname, p.contype, p.conname,
            parent_class.relname as parent_table,
            parent.attname as parent_attname
        FROM pg_attribute child
            JOIN pg_class child_class ON child_class.oid = child.attrelid
            LEFT JOIN pg_constraint p ON p.conrelid = child_class.oid
                AND child.attnum = ANY (p.conkey)
            LEFT JOIN pg_attribute parent on parent.attnum = ANY (p.confkey)
                AND parent.attrelid = p.confrelid
            LEFT JOIN pg_class parent_class on parent_class.oid = p.confrelid
        WHERE child_class.relkind = \'r\'::char
            AND child_class.relname = \''.$blueprint->table().'\'
            AND child.attnum > 0
            AND contype IS NOT NULL
        ORDER BY child.attnum
        ;';
        $relations = $this->arraify($this->connection->select($sql));

        $this->fillPrimaryKey($relations, $blueprint);
        $this->fillRelations($relations, $blueprint);

        $sql = 'SELECT * FROM pg_indexes WHERE tablename = \''.$blueprint->table().'\';';
        $indexes = $this->arraify($this->connection->select($sql));
        $this->fillIndexes($indexes, $blueprint);
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
     * @param array $relations
     * @param Blueprint $blueprint
     *
     * @todo: Support named primary keys
     */
    protected function fillPrimaryKey($relations, Blueprint $blueprint)
    {
        $columns = [];
        foreach ($relations as $row) {
            if ($row['contype'] === 'p') {
                $columns[] = $row['attname'];
            }
        }

        $blueprint->withPrimaryKey(new Index(
            Index::NAME_PRIMARY,
            '',
            $columns
        ));
    }

    /**
     * @param array $indexes
     * @param Blueprint $blueprint
     */
    protected function fillIndexes($indexes, Blueprint $blueprint)
    {
        foreach ($indexes as $row) {
            $pattern = '/\s*(UNIQUE)?\s*(KEY|INDEX)\s+(\w+)\s+\(([^\)]+)\)/mi';
            if (preg_match($pattern, $row['indexdef'], $setup) == false) {
                continue;
            }

            $indexName = Index::NAME_INDEX;

            if (strcasecmp($setup[1], 'unique') === 0) {
                $indexName = Index::NAME_UNIQUE;
            }

            $blueprint->withIndex(new Index(
                $indexName,
                $setup[3],
                $this->columnize($setup[4])
            ));
        }
    }

    /**
     * @param array $relations
     * @param Blueprint $blueprint
     *
     * @todo: Support named foreign keys
     */
    protected function fillRelations($relations, Blueprint $blueprint)
    {
        $fk = [];
        foreach ($relations as $row) {
            $relName = $row['conname'];
            if ($row['contype'] === 'f') {
                if (! array_key_exists($relName, $fk)) {
                    $fk[$relName] = [
                        'columns' =>  [],
                        'ref' => [],
                    ];
                }
                $fk[$relName]['columns'][] = $row['attname'];
                $fk[$relName]['ref'][] = $row['parent_attname'];
                $fk[$relName]['table'] = $row['parent_table'];
            }
        }

        foreach ($fk as $row) {
            $blueprint->withRelation(new Relation(
                '',
                $row['columns'],
                $row['ref'],
                [$this->schema, $row['table']],
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
        $pieces = explode('.', str_replace('\'', '', $table));

        return implode('.', array_map(function ($piece) {
            return "'$piece'";
        }, $pieces));
    }

    /**
     * @param \Illuminate\Database\Connection $connection
     * @deprecated use \Reliese\Meta\Postgres\Database::getSchemaNames
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
     * @return PostgresConnection
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
