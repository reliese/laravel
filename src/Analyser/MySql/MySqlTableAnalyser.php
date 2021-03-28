<?php

namespace Reliese\Analyser\MySql;


use Reliese\Blueprint\ColumnBlueprint;
use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Blueprint\SchemaBlueprint;
use Reliese\Blueprint\TableBlueprint;

/**
 * Class MySqlTableAnalyser
 */
class MySqlTableAnalyser
{
    use MySqlSchemaAnalyserTrait;
    use MySqlAnalyseColumnsTrait;

    /**
     * @var string
     */
    private $tableName;

    public function __construct(MySqlSchemaAnalyser $mySqlSchemaAnalyser)
    {
        $this->setMySqlSchemaAnalyser($mySqlSchemaAnalyser);
    }

    /**
     * @param SchemaBlueprint $schemaBlueprint
     * @param string          $tableName
     *
     * @return TableBlueprint
     */
    public function analyseTable(SchemaBlueprint $schemaBlueprint, string $tableName): TableBlueprint
    {
        $tableBlueprint = new TableBlueprint($schemaBlueprint, $tableName);

        $this->analyseColumns($tableBlueprint, $tableName);

        $schemaBlueprint->addTableBlueprint($tableBlueprint);
    }

}