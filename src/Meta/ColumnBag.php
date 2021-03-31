<?php

namespace Reliese\Meta;

class ColumnBag
{
    public const TYPE_BOOL = 'bool';
    public const TYPE_STRING = 'string';
    public const TYPE_INT = 'int';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $comment = '';

    /**
     * @var array
     */
    private $enum = [];

    /**
     * @var bool
     */
    private $autoincrement = false;

    /**
     * @var bool
     */
    private $unsigned = false;

    /**
     * @var bool
     */
    private $nullable = false;

    /**
     * @var mixed|null
     */
    private $default = null;

    /**
     * @var int|null
     */
    private $size = null;

    /**
     * @var int|null
     */
    private $scale = null;

    /**
     * @param string $name Column name
     * @return ColumnBag
     */
    public function withName(string $name): ColumnBag
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Use PHP boolean data type
     * @return ColumnBag
     */
    public function withTypeBool(): ColumnBag
    {
        $this->withType(static::TYPE_BOOL);

        return $this;
    }

    /**
     * Use PHP string data type
     * @return ColumnBag
     */
    public function withTypeString(): ColumnBag
    {
        $this->withType(static::TYPE_STRING);

        return $this;
    }

    /**
     * @param string $type PHP data type
     * @return ColumnBag
     */
    public function withType(string $type): ColumnBag
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param array $enum list of enum values
     * @return ColumnBag
     */
    public function withEnum(array $enum): ColumnBag
    {
        $this->enum = $enum;

        return $this;
    }

    /**
     * @todo confirm definition
     * @param int $size number of characters
     * @return ColumnBag
     */
    public function withSize(int $size): ColumnBag
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @todo confirm definition
     * @param int $scale number of decimals
     * @return ColumnBag
     */
    public function withScale(int $scale): ColumnBag
    {
        $this->scale = $scale;

        return $this;
    }

    /**
     * This column has auto incrementing behaviour
     * @return ColumnBag
     */
    public function hasAutoIncrements(): ColumnBag
    {
        $this->autoincrement = true;

        return $this;
    }

    /**
     * This column allows nulls
     * @return ColumnBag
     */
    public function isNullable(): ColumnBag
    {
        $this->nullable = true;

        return $this;
    }

    /**
     * @param mixed $default Default column value
     * @return ColumnBag
     */
    public function withDefault($default): ColumnBag
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @param string $comment Column comment
     * @return ColumnBag
     */
    public function withComment(string $comment): ColumnBag
    {
        $this->comment = $comment;

        return $this;
    }

    public function isUnsigned(): ColumnBag
    {
        $this->unsigned = true;

        return $this;
    }

    /**
     * Whether this column type is int
     *
     * @return bool
     */
    public function isTypeInt(): bool
    {
        return $this->type == static::TYPE_INT;
    }

    public function asColumn(): Column
    {
        return new Column(
            $this->name,
            $this->type,
            $this->comment,
            $this->enum,
            $this->autoincrement,
            $this->unsigned,
            $this->nullable,
            $this->default,
            $this->size,
            $this->scale
        );
    }
}
