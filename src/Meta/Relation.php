<?php


namespace Reliese\Meta;


class Relation implements HasColumns
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
     * @var string[]
     */
    private $references;

    /**
     * @var string[]
     */
    private $onTable;

    /**
     * Relation constructor.
     *
     * @param string $name
     * @param string $index
     * @param string[]  $columns
     * @param string[]  $references
     * @param string[]  $onTable
     */
    public function __construct(string $name, string $index, array $columns, array $references, array $onTable)
    {
        $this->name = $name;
        $this->index = $index;
        $this->columns = $columns;
        $this->references = $references;
        $this->onTable = $onTable;
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
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return string[]
     */
    public function getReferences(): array
    {
        return $this->references;
    }

    /**
     * @return string[]
     */
    public function getOnTable(): array
    {
        return $this->onTable;
    }
}
