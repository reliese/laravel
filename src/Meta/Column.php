<?php

namespace Reliese\Meta;

class Column
{
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
    private $comment;

    /**
     * @var array
     */
    private $enum;

    /**
     * @var bool
     */
    private $autoincrement;

    /**
     * @var bool
     */
    private $unsigned;

    /**
     * @var bool
     */
    private $nullable;

    /**
     * @var mixed|null
     */
    private $default;

    /**
     * @var int|null
     */
    private $size;

    /**
     * @var int|null
     */
    private $scale;

    /**
     * Column constructor.
     *
     * @param string $name
     * @param string $type
     * @param array $enum
     * @param bool $autoincrement
     * @param bool $unsigned
     * @param bool $nullable
     * @param mixed|null $default
     * @param string $comment
     * @param int|null $size
     * @param int|null $scale
     */
    public function __construct(
        string $name,
        string $type,
        string $comment,
        array $enum,
        bool $autoincrement,
        bool $unsigned,
        bool $nullable,
        $default = null,
        ?int $size = null,
        ?int $scale = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->comment = $comment;
        $this->enum = $enum;
        $this->autoincrement = $autoincrement;
        $this->unsigned = $unsigned;
        $this->nullable = $nullable;
        $this->default = $default;
        $this->size = $size;
        $this->scale = $scale;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getEnum(): array
    {
        return $this->enum;
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @return int|null
     */
    public function getScale(): ?int
    {
        return $this->scale;
    }

    /**
     * @return bool
     */
    public function isAutoincrement(): bool
    {
        return $this->autoincrement;
    }

    /**
     * @return bool
     */
    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return mixed|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }
}
