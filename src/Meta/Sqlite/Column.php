<?php

/**
 * Created by Cristian.
 * Date: 18/09/16 08:36 PM.
 */

namespace Reliese\Meta\Sqlite;

use Doctrine\DBAL\Schema\Column as DoctrineColumn;
use Reliese\Meta\ColumnBag;

class Column implements \Reliese\Meta\ColumnParser
{
    /**
     * @var DoctrineColumn
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
     * @param DoctrineColumn $metadata
     */
    public function __construct(DoctrineColumn $metadata)
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
        $dataType = $this->metadata->getType()->getName();

        foreach (static::$mappings as $phpType => $database) {
            if (in_array($dataType, $database)) {
                $attributes->withType($phpType);
            }
        }

        if ($attributes->isTypeInt() && $this->metadata->getUnsigned()) {
            $attributes->isUnsigned();
        }
    }

    /**
     * @param ColumnBag $attributes
     */
    protected function parseName(ColumnBag $attributes)
    {
        $attributes->withName($this->metadata->getName());
    }

    /**
     * @param ColumnBag $attributes
     */
    protected function parseAutoincrement(ColumnBag $attributes)
    {
        if ($this->metadata->getAutoincrement()) {
            $attributes->hasAutoIncrements();
        }
    }

    /**
     * @param ColumnBag $attributes
     */
    protected function parseNullable(ColumnBag $attributes)
    {
        if (!$this->metadata->getNotnull()) {
            $attributes->isNullable();
        }
    }

    /**
     * @param ColumnBag $attributes
     */
    protected function parseDefault(ColumnBag $attributes)
    {
        $attributes->withDefault($this->metadata->getDefault());
    }

    /**
     * @param ColumnBag $attributes
     */
    protected function parseComment(ColumnBag $attributes)
    {
        $attributes->withComment($this->metadata->getComment());
    }
}
