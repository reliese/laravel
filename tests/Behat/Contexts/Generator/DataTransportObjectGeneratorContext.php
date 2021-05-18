<?php

namespace Tests\Behat\Contexts\Generator;

use Reliese\Generator\DataTransport\DataTransportObjectGenerator;
/**
 * Class DataTransportObjectGeneratorContext
 */
class DataTransportObjectGeneratorContext extends GeneratorContexts
{
    /**
     * @When /^DataTransportObjectGenerator generates Abstract Dto from Schema "([^"]*)" Table "([^"]*)"$/
     */
    public function generateAbstractDtoClassDefinitionFromTable($schemaName, $tableName)
    {
//        $schemaBlueprint = $this
//            ->getBlueprintContext()
//            ->getBlueprintSchemaContext()
//            ->getSchemaBlueprint($schemaName);
//
//        $tableBlueprint = $schemaBlueprint->getTableBlueprint($tableName);
//
//        $abstractClassDefinition = $this->getDataTransportObjectGenerator()->generateAbstractClass();

    }
}