<?php

namespace Reliese\Blueprint;

/**
 * Class ForeignKeyBlueprint
 */
class ForeignKeyBlueprint
{
    private string $name;

    /**
     * @var ColumnBlueprint[]
     */
    private array $referencedColumns;

    /**
     * @var ColumnOwnerInterface
     */
    private ColumnOwnerInterface $referencedTable;

    /**
     * @var ColumnBlueprint[]
     */
    private array $referencingColumns;

    /**
     * @var ColumnOwnerInterface
     */
    private ColumnOwnerInterface $tableOrViewBlueprint;

    /**
     * ForeignKeyConstraintBlueprint constructor.
     *
     * @param ColumnOwnerInterface $columnOwner
     * @param string $name
     * @param ColumnBlueprint[] $referencingColumns
     * @param ColumnOwnerInterface $referencedTable
     * @param ColumnBlueprint[] $referencedColumns
     */
    public function __construct(
        ColumnOwnerInterface $columnOwner,
        string $name,
        array $referencingColumns,
        ColumnOwnerInterface $referencedTable,
        array $referencedColumns
    )
    {
        $this->tableOrViewBlueprint = $columnOwner;
        $this->name = $name;
        $this->referencingColumns = $referencingColumns;
        $this->referencedTable = $referencedTable;
        $this->referencedColumns = $referencedColumns;

        foreach ($this->referencingColumns as $referencingColumn) {
            $referencingColumn->addReferencingForeignKey($this);
        }
        
        foreach ($this->referencedColumns as $referencedColumn) {
            $referencedColumn->addReferencedForeignKey($this);
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the name of the table or view that is referenced by this key
     *
     * @return string
     */
    public function getReferencedTableName(): string
    {
        return $this->referencedTable->getUniqueName();
    }

    /**
     * Returns the name of the table or view that created this foreign key
     *
     * @return string
     */
    public function getReferencingObjectName(): string
    {
        return $this->tableOrViewBlueprint->getUniqueName();
    }

    public function getReferencingColumnNames(): array
    {
        return \array_keys($this->referencingColumns);
    }

    /**
     * @return ColumnOwnerInterface
     */
    public function getReferencedTableBlueprint() : ColumnOwnerInterface
    {
        return $this->referencedTable;
    }

    /**
     * @return ColumnBlueprint[]
     */
    public function getReferencedColumns() : array
    {
        return $this->referencedColumns;
    }

    public function getFkColumnPairs():array
    {
        $columns = [];
        $i = -1;
        foreach ($this->referencingColumns as $referencingColumn) {
            $i++;
            $columns[$i][0] = $referencingColumn;
        }
        $i = -1;
        foreach ($this->referencedColumns as $referencedColumn) {
            $i++;
            $columns[$i][1] = $referencedColumn;
        }
        return $columns;
    }
}
