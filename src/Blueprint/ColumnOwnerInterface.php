<?php

namespace Reliese\Blueprint;

/**
 * Interface ColumnOwnerInterface
 *
 * @package Reliese\Blueprint
 */
interface ColumnOwnerInterface
{
    /**
     * @param ColumnBlueprint $columnBlueprint
     */
    public function addColumnBlueprint(ColumnBlueprint $columnBlueprint);

    /**
     * @param string $columnName
     *
     * @return ColumnBlueprint
     */
    public function getColumnBlueprint(string $columnName): ColumnBlueprint;

    /**
     * @return ColumnBlueprint[]
     */
    public function getColumnBlueprints(): array;

    /**
     * @return string[]
     */
    public function getColumnNames(): array;

    /**
     * returns "Schema.[TableName |View Name]"
     *
     * @return string
     */
    public function getUniqueName(): string;
}
