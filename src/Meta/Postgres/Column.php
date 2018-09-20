<?php

/**
 * Created by Cristian.
 * Date: 18/09/16 08:36 PM.
 */

namespace Reliese\Meta\Postgres;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Fluent;

class Column implements \Reliese\Meta\Column
{
    /**
     * @var array
     */
    protected $metadata;

    /**
     * @var array
     */
    protected $metas = [
        'type', 'name', 'autoincrement', 'nullable', 'default', 'comment',
    ];

    /**
     * @var array
     */
    public static $mappings = [
        'string' => [
            'char',
            'character',
            'character varying',
            'json',
            'jsonb',
            'varchar',
            'text',
        ],
        'date' => [
            'date',
            'time',
            'time',
            'timestamp',
            'timestamp',
            'timestamptz',
            'timetz',
        ],
        'int' => [
            'real',
            'bigint',
            'bigserial',
            'int',
            'int4',
            'int2',
            'int8',
            'integer',
            'serial',
            'serial2',
            'serial4',
            'serial8',
            'smallint',
            'smallserial',
        ],
        'float' => [
            'numeric',
            'decimal',
            'double precision',
            'float4',
            'float8',
        ],
        'boolean' => [
            'bool',
            'boolean',
        ],
    ];

    /*/

    note for working on dynamics or better list later.
    Also need support for array of a field type

    SELECT n.nspname as "Schema",
      pg_catalog.format_type(t.oid, NULL) AS "Name",
      pg_catalog.obj_description(t.oid, 'pg_type') as "Description"
    FROM pg_catalog.pg_type t
         LEFT JOIN pg_catalog.pg_namespace n ON n.oid = t.typnamespace
    WHERE (t.typrelid = 0 OR (SELECT c.relkind = 'c' FROM pg_catalog.pg_class c WHERE c.oid = t.typrelid))
      AND NOT EXISTS(SELECT 1 FROM pg_catalog.pg_type el WHERE el.oid = t.typelem AND el.typarray = t.oid)
      AND pg_catalog.pg_type_is_visible(t.oid)
    ORDER BY 1, 2;

    /**/
    public static $ungroupedFieldTypes = [
        'bit',
        'bit varying',
        'box',
        'bytea',
        'cidr',
        'circle',
        'inet',
        'interval',
        'line',
        'lseg',
        'macaddr',
        'money',
        'path',
        'pg_lsn',
        'point',
        'polygon',
        'tsquery',
        'tsvector',
        'txid_snapshot',
        'uuid',
        'varbit',
        'xml',
    ];

    /**
     * PostgresColumn constructor.
     *
     * @param array $metadata
     */
    public function __construct($metadata = [])
    {
        $this->metadata = $metadata;
    }

    /**
     * @return \Illuminate\Support\Fluent
     */
    public function normalize()
    {
        $attributes = new Fluent();

        foreach ($this->metas as $meta) {
            $this->{'parse'.ucfirst($meta)}($attributes);
        }

        return $attributes;
    }

    /**
     * @param \Illuminate\Support\Fluent $attributes
     */
    protected function parseType(Fluent $attributes)
    {
        $type = $this->get('data_type', 'string');

        preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $type, $matches);

        $dataType = strtolower($matches[1]);
        $attributes['type'] = $dataType;

        foreach (static::$mappings as $phpType => $database) {
            if (in_array($dataType, $database)) {
                $attributes['type'] = $phpType;
            }
        }

        if (isset($matches[2])) {
            $this->parsePrecision($dataType, $matches[2], $attributes);
        }

        if ($attributes['type'] == 'int') {
            $attributes['unsigned'] = Str::contains($type, 'unsigned');
        }
    }

    /**
     * @param string                     $databaseType
     * @param string                     $precision
     * @param \Illuminate\Support\Fluent $attributes
     */
    protected function parsePrecision($databaseType, $precision, Fluent $attributes)
    {
        $precision = explode(',', str_replace("'", '', $precision));

        // Check whether it's an enum
        if ($databaseType == 'enum') {
            $attributes['enum'] = $precision;

            return;
        }

        $size = (int) current($precision);

        // Check whether it's a boolean
        if ($size == 1 && in_array($databaseType, ['bit', 'tinyint'])) {
            // Make sure this column type is a boolean
            $attributes['type'] = 'bool';

            if ($databaseType == 'bit') {
                $attributes['mappings'] = ["\x00" => false, "\x01" => true];
            }

            return;
        }

        $attributes['size'] = $size;

        if ($scale = next($precision)) {
            $attributes['scale'] = (int) $scale;
        }
    }

    /**
     * @param \Illuminate\Support\Fluent $attributes
     */
    protected function parseName(Fluent $attributes)
    {
        $attributes['name'] = $this->get('column_name');
    }

    /**
     * @param \Illuminate\Support\Fluent $attributes
     */
    protected function parseAutoincrement(Fluent $attributes)
    {
        $attributes['autoincrement'] = $this->defaultIsNextVal($attributes);
        if ($this->same('column_default', 'auto_increment')) {
            $attributes['autoincrement'] = true;
        }
    }

    /**
     * @param \Illuminate\Support\Fluent $attributes
     */
    protected function parseNullable(Fluent $attributes)
    {
        $attributes['nullable'] = $this->same('is_nullable', 'YES');
    }

    /**
     * @param \Illuminate\Support\Fluent $attributes
     */
    protected function parseDefault(Fluent $attributes)
    {
        $value = null;
        if ($this->defaultIsNextVal($attributes)) {
            $attributes['autoincrement'] = true;
        } else {
            $value = $this->get('column_default', $this->get('generation_expression', null));
        }
        $attributes['default'] = $value;
    }

    /**
     * @param \Illuminate\Support\Fluent $attributes
     */
    protected function parseComment(Fluent $attributes)
    {
        $attributes['comment'] = $this->get('description');
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function get($key, $default = null)
    {
        return Arr::get($this->metadata, $key, $default);
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    protected function same($key, $value)
    {
        return strcasecmp($this->get($key, ''), $value) === 0;
    }

    /**
     * @param \Illuminate\Support\Fluent $attributes
     *
     * @return bool
     */
    private function defaultIsNextVal(Fluent $attributes)
    {
        return
            preg_match('/serial/i', $this->get('data_type', ''))
            ||
            preg_match(
                '/nextval\(/i',
                $this->get('column_default', $this->get('generation_expression', null))
            );
    }
}
