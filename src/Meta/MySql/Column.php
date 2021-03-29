<?php

/**
 * Created by Cristian.
 * Date: 18/09/16 08:36 PM.
 */

namespace Reliese\Meta\MySql;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Reliese\Meta\ColumnBag;
use Reliese\Meta\ColumnParser;

class Column implements ColumnParser
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
     * @return \Reliese\Meta\Column
     */
    public function normalize(): \Reliese\Meta\Column
    {
        $attributes = new ColumnBag();

        foreach ($this->metas as $meta) {
            $this->{'parse'.ucfirst($meta)}($attributes);
        }

        return $attributes->asColumn();
    }

    /**
     * @param ColumnBag $attributes
     */
    protected function parseType(ColumnBag $attributes)
    {
        $type = $this->get('Type', 'string');

        preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $type, $matches);

        $dataType = strtolower($matches[1]);
        $attributes->withType($dataType);

        foreach (static::$mappings as $phpType => $database) {
            if (in_array($dataType, $database)) {
                $attributes->withType($phpType);
            }
        }

        if (isset($matches[2])) {
            $this->parsePrecision($dataType, $matches[2], $attributes);
        }

        if ($attributes->isTypeInt() && Str::contains($type, 'unsigned')) {
            $attributes->isUnsigned();
        }
    }

    /**
     * @param string $databaseType
     * @param string $precision
     * @param ColumnBag $attributes
     */
    protected function parsePrecision(string $databaseType, string $precision, ColumnBag $attributes)
    {
        $precision = explode(',', str_replace("'", '', $precision));

        // Check whether it's an enum
        if ($databaseType == 'enum') {
            $attributes->withEnum($precision);

            return;
        }

        $size = (int) current($precision);

        // Check whether it's a boolean
        if ($size == 1 && in_array($databaseType, ['bit', 'tinyint'])) {
            // Make sure this column type is a boolean
            $attributes->withTypeBool();

            return;
        }

        $attributes->withSize($size);

        if ($scale = next($precision)) {
            $attributes->withScale((int) $scale);
        }
    }

    /**
     * @param ColumnBag $attributes
     */
    protected function parseName(ColumnBag $attributes)
    {
        $attributes->withName($this->get('Field'));
    }

    /**
     * @param ColumnBag $attributes
     */
    protected function parseAutoincrement(ColumnBag $attributes)
    {
        if ($this->same('Extra', 'auto_increment')) {
            $attributes->hasAutoIncrements();
        }
    }

    /**
     * @param ColumnBag $attributes
     */
    protected function parseNullable(ColumnBag $attributes)
    {
        if ($this->same('Null', 'YES')) {
            $attributes->isNullable();
        }
    }

    /**
     * @param ColumnBag $attributes
     */
    protected function parseDefault(ColumnBag $attributes)
    {
        $attributes->withDefault($this->get('Default'));
    }

    /**
     * @param ColumnBag $attributes
     */
    protected function parseComment(ColumnBag $attributes)
    {
        $attributes->withComment((string) $this->get('Comment'));
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function get(string $key, $default = null)
    {
        return Arr::get($this->metadata, $key, $default);
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    protected function same(string $key, string $value)
    {
        return strcasecmp($this->get($key, ''), $value) === 0;
    }
}
