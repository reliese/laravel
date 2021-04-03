<?php

namespace Reliese\Blueprint;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
/**
 * Class ForeignKeyBlueprint
 */
class ForeignKeyBlueprint
{
    /**
     * @var ColumnOwnerInterface
     */
    private $columnOwner;

    private $name;

    /**
     * @var array
     */
    private $referencedColumns;

    /**
     * @var ColumnOwnerInterface
     */
    private $referencedTable;

    /**
     * @var array
     */
    private $referencingColumns;

    /**
     * ForeignKeyConstraintBlueprint constructor.
     *
     * @param ColumnOwnerInterface $columnOwner
     * @param string $name
     * @param string[] $referencingColumns
     * @param ColumnOwnerInterface $referencedTable
     * @param string[] $referencedColumns
     */
    public function __construct(
        ColumnOwnerInterface $columnOwner,
        string $name,
        array $referencingColumns,
        ColumnOwnerInterface $referencedTable,
        array $referencedColumns
    ) {
        $this->columnOwner = $columnOwner;
        $this->name = $name;
        $this->referencingColumns = $referencingColumns;
        $this->referencedTable = $referencedTable;
        $this->referencedColumns = $referencedColumns;
    }

    /**
     * @return string
     */
    public function getName():string
    {
        return $this->name;
    }
}
