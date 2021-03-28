<?php

namespace Reliese\Analyser\MySql;

use Reliese\Blueprint\SchemaBlueprint;
use Reliese\Blueprint\TableBlueprint;
use Reliese\Blueprint\ViewBlueprint;
/**
 * Class MySqlViewAnalyser
 */
class MySqlViewAnalyser
{
    use MySqlSchemaAnalyserTrait;
    use MySqlAnalyseColumnsTrait;

    /**
     * MySqlViewAnalyser constructor.
     *
     * @param MySqlSchemaAnalyser $mySqlSchemaAnalyser
     */
    public function __construct(MySqlSchemaAnalyser $mySqlSchemaAnalyser)
    {
        $this->setMySqlSchemaAnalyser($mySqlSchemaAnalyser);
    }

    /**
     * @var MySqlColumnAnalyser[]
     */
    private $mySqlColumnAnalysers = [];


    /**
     * @param SchemaBlueprint $schemaBlueprint
     * @param string          $viewName
     *
     * @return ViewBlueprint
     */
    public function analyseView(SchemaBlueprint $schemaBlueprint, string $viewName): ViewBlueprint
    {
        $viewBlueprint = new ViewBlueprint($schemaBlueprint, $viewName);

        $this->analyseColumns($viewBlueprint, $viewName);

        $schemaBlueprint->addViewBlueprint($viewBlueprint);
    }
}