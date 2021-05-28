<?php

namespace Reliese\Blueprint;

/**
 * Interface ColumnOwnerInterface
 *
 * @package Reliese\Blueprint
 */
interface ColumnOwnerInterface extends SchemaMemberInterface
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
}
