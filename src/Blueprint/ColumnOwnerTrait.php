<?php

namespace Reliese\Blueprint;

use InvalidArgumentException;/**
 * Trait ColumnOwnerTrait
 * Generic implemenation of the ColumnOwnerInterface
 *
 * @package Reliese\Blueprint
 */
trait ColumnOwnerTrait
{
    /**
     * @var ColumnBlueprint[]
     */
    private $columnBlueprints = [];

    /**
     * @param ColumnBlueprint $columnBlueprint
     */
    public function addColumnBlueprint(ColumnBlueprint $columnBlueprint)
    {
        $this->columnBlueprints[$columnBlueprint->getColumnName()] = $columnBlueprint;
    }

    /**
     * @param string $columnName
     *
     * @return ColumnBlueprint
     */
    public function getColumnBlueprint(string $columnName): ColumnBlueprint
    {
        if (!\array_key_exists($columnName, $this->columnBlueprints)) {
            throw new InvalidArgumentException("Unable to locate a column named \"$columnName\"");
        }

        return $this->columnBlueprints[$columnName];
    }

    /**
     * @param array $columnBlueprints
     *
     * @return $this
     */
    public function addColumnBlueprints(array $columnBlueprints) : static
    {
        if (empty($columnBlueprints)) {
            return $this;
        }
        foreach ($columnBlueprints as $columnBlueprint) {
            $this->addColumnBlueprint($columnBlueprint);
        }
        return $this;
    }

    /**
     * @return ColumnBlueprint[]
     */
    public function getColumnBlueprints(): array
    {
        return $this->columnBlueprints;
    }

    /**
     * @return string[]
     */
    public function getColumnNames(): array
    {
        return \array_keys($this->columnBlueprints);
    }

}
