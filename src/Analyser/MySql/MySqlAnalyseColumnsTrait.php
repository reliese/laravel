<?php

namespace Reliese\Analyser\MySql;

use Illuminate\Database\MySqlConnection;
use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\ColumnOwnerInterface;
/**
 * Trait AnalyseColumnsTrait
 */
trait MySqlAnalyseColumnsTrait
{
    /**
     * @return string
     */
    abstract public function getSchemaName(): string;

    /**
     * @return MySqlConnection
     */
    abstract public function getConnection(): MySqlConnection;

    /**
     * @param ColumnOwnerInterface $columnOwner
     * @param string $tableOrViewName
     */
    public function analyseColumns(ColumnOwnerInterface $columnOwner, string $tableOrViewName)
    {
        /** @noinspection SqlNoDataSourceInspection */
        $sql = "
SELECT
    COLUMN_NAME as column_name,
    DATA_TYPE as data_type,
    case IS_NULLABLE WHEN 'YES' THEN '1' else '0' end as is_nullable,
    IFNULL(CHARACTER_MAXIMUM_LENGTH, 'null') as maximum_characters,
    IFNULL(NUMERIC_PRECISION, 'null') as numeric_precision,
    IFNULL(NUMERIC_SCALE, 'null') as numeric_scale,
    CASE WHEN EXTRA like '%auto_increment%' THEN '1' else '0' end as is_autoincrement,
    CASE WHEN COLUMN_DEFAULT IS NOT NULL THEN '1' else '0' end as has_default
FROM
     information_schema.COLUMNS as C
WHERE
    C.TABLE_SCHEMA = '{$this->getSchemaName()}'
AND C.TABLE_NAME = '{$tableOrViewName}'
";

        $results = [];
        foreach ($this->getConnection()->select($sql) as $row) {
            $columnBlueprint = new ColumnBlueprint($columnOwner,
                $row->column_name,
                \Reliese\Analyser\MySql\MySqlTools::getPhpDataTypeFromDatabaseDataType($row->DATA_TYPE),
                $row->is_nullable,
                $row->maximum_characters,
                $row->numeric_precision,
                $row->numeric_scale,
                $row->is_autoincrement,
                $row->has_default
            // Comments
            // Enums
            // Unsigned
            );

            $columnOwner->addColumnBlueprint($columnBlueprint);
        }
    }
}