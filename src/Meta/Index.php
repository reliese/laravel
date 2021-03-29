<?php

namespace Reliese\Meta;

class Index implements HasColumns
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $index;

    /**
     * @var string[]
     */
    private $columns;

    /**
     * Index constructor.
     *
     * @param string $name
     * @param string $index
     * @param string[]  $columns
     */
    public function __construct(string $name, string $index, array $columns)
    {
        $this->name = $name;
        $this->index = $index;
        $this->columns = $columns;
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
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @return string[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }
}
