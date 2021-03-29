<?php

namespace Reliese\Meta\Postgres;

use Illuminate\Support\Arr;
use Reliese\Meta\ColumnBag;

/**
 * Created by rwdim from cristians MySql original.
 * Date: 25/08/18 04:20 PM.
 */
class Column implements \Reliese\Meta\ColumnParser
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
        $dataType = $this->get('data_type', 'string');
        $attributes->withType($dataType);

        foreach (static::$mappings as $phpType => $database) {
            if (in_array($dataType, $database)) {
                $attributes->withType($phpType);
            }
        }

        $this->parsePrecision($dataType, $attributes);
    }

    /**
     * @param string $databaseType
     * @param ColumnBag $attributes
     *
     * @todo handle non numeric precisions
     */
    protected function parsePrecision(string $databaseType, ColumnBag $attributes)
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
        $attributes->withName($this->get('column_name'));
    }

    /**
     * @param ColumnBag $attributes
     * @todo
     */
    protected function parseAutoincrement(ColumnBag $attributes)
    {
        $hasAutoincrements = preg_match('/serial/i', $this->get('data_type', ''))
            || $this->defaultIsNextVal();

        if ($hasAutoincrements) {
            $attributes->hasAutoIncrements();
        }
    }

    /**
     * @param ColumnBag $attributes
     */
    protected function parseNullable(ColumnBag $attributes)
    {
        if ($this->same('is_nullable', 'YES')) {
            $attributes->isNullable();
        }
    }

    /**
     * @param ColumnBag $attributes
     */
    protected function parseDefault(ColumnBag $attributes)
    {
        $value = null;
        if ($this->defaultIsNextVal()) {
            $attributes->hasAutoIncrements();
        } else {
            $value = $this->get('column_default', $this->get('generation_expression', null));
        }

        $attributes->withDefault($value);
    }

    /**
     * @param ColumnBag $attributes
     * @todo
     */
    protected function parseComment(ColumnBag $attributes)
    {
        $attributes->withComment($this->get('Comment'));
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
    protected function same(string $key, string $value): bool
    {
        return strcasecmp($this->get($key, ''), $value) === 0;
    }

    /**
     * @return bool
     */
    private function defaultIsNextVal(): bool
    {
        $value = $this->get('column_default', $this->get('generation_expression', null));

        return preg_match('/nextval\(/i', $value);
    }
}
