<?php

namespace Reliese\Meta\Postgres;

use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;

/**
 * Created by rwdim from cristians MySql original.
 * Date: 25/08/18 04:20 PM.
 */
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
     * @todo check these
     */
    public static $mappings = [
        'string' => ['varchar', 'text', 'string', 'char', 'enum', 'tinytext', 'mediumtext', 'longtext', 'json'],
        'date' => ['datetime', 'year', 'date', 'time', 'timestamp'],
        'int' => ['int', 'integer', 'tinyint', 'smallint', 'mediumint', 'bigint', 'bigserial', 'serial', 'smallserial', 'tinyserial', 'serial4', 'serial8'],
        'float' => ['float', 'decimal', 'numeric', 'dec', 'fixed', 'double', 'real', 'double precision'],
        'boolean' => ['boolean', 'bool', 'bit'],
        'binary' => ['blob', 'longblob', 'jsonb'],
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
        $dataType = $this->get('data_type', 'string');
        $attributes['type'] = $dataType;

        foreach (static::$mappings as $phpType => $database) {
            if (in_array($dataType, $database)) {
                $attributes['type'] = $phpType;
            }
        }

        $this->parsePrecision($dataType, $attributes);
    }

    /**
     * @param string $databaseType
     * @param \Illuminate\Support\Fluent $attributes
     * @todo handle non numeric precisions
     */
    protected function parsePrecision($databaseType, Fluent $attributes)
    {
        $precision = $this->get('numeric_precision', 'string');
        $precision = explode(',', str_replace("'", '', $precision));

        // Check whether it's an enum
        if ($databaseType == 'enum') {
            //$attributes['enum'] = $precision; //todo

            return;
        }

        $size = (int) $precision;

        // Check whether it's a boolean
        if ($size == 1 && in_array($databaseType, self::$mappings['boolean'])) {
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
     * @todo
     */
    protected function parseAutoincrement(Fluent $attributes)
    {
        $attributes['autoincrement'] = preg_match('/serial/i',
            $this->get('data_type', '')) || $this->defaultIsNextVal($attributes);
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
     * @todo
     */
    protected function parseComment(Fluent $attributes)
    {
        $attributes['comment'] = $this->get('Comment');
    }

    /**
     * @param string $key
     * @param mixed $default
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
        $value = $this->get('column_default', $this->get('generation_expression', null));

        return preg_match('/nextval\(/i', $value);
    }
}
