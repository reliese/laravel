<?php

namespace Reliese\Meta\Postgres;

use Illuminate\Support\Arr;
use Reliese\Meta\Blueprint;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;

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
     * @var \Illuminate\Database\PostgresConnection
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
     * Mapper constructor.
     *
     * @param string $schema
     * @param \Illuminate\Database\PostgresConnection $connection
     */
    public function __construct($schema, $connection)
    {

// print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 ) );
        $this->schema = $schema;
        $this->connection = $connection;

        $this->load();

    }

    /**
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
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
// print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 4 ) );
echo __CLASS__.':'.__FUNCTION__.' : '.$this->schema." : ".count( $tables ) . " : ". count( $this->tables ) ."\n";
        foreach ($tables as $table) {
            $blueprint = new Blueprint($this->connection->getName(), $this->schema, $table);
            $this->fillColumns($blueprint);
            $this->fillConstraints($blueprint);
            $this->tables[$table] = $blueprint;
        }

    }

    /**
     * @param string $schema
     *
     * @return array
     */
    protected function fetchTables($schema)
    {

        $sql = "SELECT * FROM information_schema.tables WHERE table_schema = :schema";

        $params = [
            'schema'  => $schema,
        ];

        $rows = $this->arraify($this->connection->select($sql, $params ));

        $names = array_column($rows, 'table_name');

        return Arr::flatten($names);

    }

    /**
     * @param \Reliese\Meta\Blueprint $blueprint
     */
    protected function fillColumns(Blueprint $blueprint)
    {

        $sql = "
SELECT
    *
FROM
    information_schema.columns
WHERE
        table_schema = :schema
  AND   table_name   = :table
";

        list( $schema, $table ) = explode( '.', $blueprint->qualifiedTable() );

        $params = [
            'schema'    => $schema,
            'table'     => $table,
        ];

        $rows = $this->arraify($this->connection->select($sql, $params ));

        foreach ($rows as $column) {
            $blueprint->withColumn(
                $this->parseColumn($column)
            );
        }
    }

    /**
     * @param array $metadata
     *
     * @return \Illuminate\Support\Fluent
     */
    protected function parseColumn($metadata)
    {
        return (new Column($metadata))->normalize();
    }

    /**
     * @param \Reliese\Meta\Blueprint $blueprint
     */
    protected function fillConstraints(Blueprint $blueprint)
    {

        $this->fillPrimaryKey( "", $blueprint);
        $this->fillIndexes("", $blueprint);
        $this->fillRelations("", $blueprint);

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
     * @param string $sql
     * @param \Reliese\Meta\Blueprint $blueprint
     * @todo: Support named primary keys
     */
    protected function fillPrimaryKey($sql, Blueprint $blueprint)
    {

        $fullTable = $blueprint->qualifiedTable();

        $sql = "
SELECT a.attname
FROM   pg_index i
JOIN   pg_attribute a ON a.attrelid = i.indrelid
                     AND a.attnum = ANY(i.indkey)
WHERE  i.indrelid = '$fullTable'::regclass
AND    i.indisprimary;
";

        $res = $this->arraify( $this->connection->select( $sql ) );

        foreach( $res as $row ) {

            $blueprint->withPrimaryKey(new Fluent([
                'name'    => 'primary',
                'index'   => '',
                'columns' => [ $row['attname'] ],
            ]));

        }

    }

    /**
     * @param string $sql
     * @param \Reliese\Meta\Blueprint $blueprint
     */
    protected function fillIndexes($sql, Blueprint $blueprint)
    {

        $fullTable = $blueprint->qualifiedTable();

        $sql = "
SELECT
    ix.indisunique  AS is_unique,
    ix.indisprimary AS is_primary,
    t.relname       AS table_name,
    i.relname       AS index_name,
    a.attname       AS column_name
FROM
    pg_class     t,
    pg_class     i,
    pg_index     ix,
    pg_attribute a
WHERE
        t.oid       = ix.indrelid
    AND i.oid       = ix.indexrelid
    AND a.attrelid  = t.oid
    AND a.attnum    = ANY(ix.indkey)
    AND t.relkind   = 'r'
    AND ix.indrelid = '$fullTable'::regclass
ORDER BY
    t.relname,
    i.relname;
";

        $row = $this->arraify( $this->connection->select( $sql ) );

        foreach ($row as $setup) {
            $index = [
                'name' => ( $setup["is_unique"] == true ? 'unique' : 'index' ),
                'columns' => $this->columnize($setup['column_name']),
                'index' => $setup['index_name'],
            ];
            $blueprint->withIndex(new Fluent($index));
        }

    }

    /**
     * @param string $sql
     * @param \Reliese\Meta\Blueprint $blueprint
     * @todo: Support named foreign keys
     */
    protected function fillRelations($sql, Blueprint $blueprint)
    {

        $fullTable = $blueprint->qualifiedTable();

        $sql = "
SELECT
    tc.table_schema,
    tc.constraint_name,
    tc.table_name,
    kcu.column_name,
    ccu.table_schema AS foreign_table_schema,
    ccu.table_name   AS foreign_table_name,
    ccu.column_name  AS foreign_column_name
FROM
            information_schema.table_constraints        AS tc
    JOIN    information_schema.key_column_usage         AS kcu  ON tc.constraint_name = kcu.constraint_name
    JOIN    information_schema.constraint_column_usage  AS ccu  ON ccu.constraint_name = tc.constraint_name
WHERE
        constraint_type = 'FOREIGN KEY'
    AND tc.table_name = '$fullTable';
";

        $relations = $this->arraify( $this->connection->select( $sql ) );

        foreach ($relations as $setup) {
            $table = $setup['table_name'];

            $relation = [
                'name' => 'foreign',
                'index' => '',
                'columns' => $this->columnize($setup['column_name']),
                'references' => $this->columnize($setup['foreign_column_name']),
                'on' => $table,
            ];

            $blueprint->withRelation(new Fluent($relation));
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
     * @param \Reliese\Meta\Blueprint $blueprint
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
        $schemas = $connection->getDoctrineSchemaManager()->getSchemaNames();

        return array_diff($schemas, [
            'information_schema',
            'sys',
            'pgsql',
            'postgres',
            'performance_schema',
        ]);
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
    public function has($table)
    {
        return array_key_exists($table, $this->tables);
    }

    /**
     * @return \Reliese\Meta\Blueprint[]
     */
    public function tables()
    {
        return $this->tables;
    }

    /**
     * @param string $table
     *
     * @return \Reliese\Meta\Blueprint
     */
    public function table($table)
    {
        if (! $this->has($table)) {
            throw new \InvalidArgumentException("Table [$table] does not belong to schema [{$this->schema}]");
        }

        return $this->tables[$table];
    }

    /**
     * @return \Illuminate\Database\PostgresConnection
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * @param \Reliese\Meta\Blueprint $table
     *
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
