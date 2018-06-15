<?php

/**
 * Created by Cristian.
 * Date: 18/09/16 08:36 PM.
 */

namespace Reliese\Meta\MySql;

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
        'string' => ['varchar', 'text', 'string', 'char', 'enum', 'tinytext', 'mediumtext', 'longtext'],
        'date' => ['datetime', 'year', 'date', 'time', 'timestamp'],
        'int' => ['bigint', 'int', 'integer', 'tinyint', 'smallint', 'mediumint'],
        'float' => ['float', 'decimal', 'numeric', 'dec', 'fixed', 'double', 'real', 'double precision'],
        'boolean' => ['longblob', 'blob', 'bit'],
    ];

    /**
     * MysqlColumn constructor.
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
        $type = $this->get('Type', 'string');

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
     * @param string $databaseType
     * @param string $precision
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
        $attributes['name'] = $this->get('Field');
    }

    /**
     * @param \Illuminate\Support\Fluent $attributes
     */
    protected function parseAutoincrement(Fluent $attributes)
    {
        if ($this->same('Extra', 'auto_increment')) {
            $attributes['autoincrement'] = true;
        }
    }

    /**
     * @param \Illuminate\Support\Fluent $attributes
     */
    protected function parseNullable(Fluent $attributes)
    {
        $attributes['nullable'] = $this->same('Null', 'YES');
    }

    /**
     * @param \Illuminate\Support\Fluent $attributes
     */
    protected function parseDefault(Fluent $attributes)
    {
        $attributes['default'] = $this->get('Default');
    }

    /**
     * @param \Illuminate\Support\Fluent $attributes
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
}
