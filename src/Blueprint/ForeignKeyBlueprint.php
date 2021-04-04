<?php

namespace Reliese\Blueprint;

/**
 * Class ForeignKeyBlueprint
 */
class ForeignKeyBlueprint
{
    private $name;

    /**
     * @var ColumnBlueprint[]
     */
    private array $referencedColumns = [];

    /**
     * @var ColumnOwnerInterface
     */
    private ColumnOwnerInterface $referencedTable;

    /**
     * @var ColumnBlueprint[]
     */
    private array $referencingColumns = [];

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
    public function __construct(ColumnOwnerInterface $columnOwner,
        string $name,
        array $referencingColumns,
        ColumnOwnerInterface $referencedTable,
        array $referencedColumns)
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
}
