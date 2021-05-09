<?php

namespace Tests\Behat\Contexts\Generator;

use Behat\Behat\Tester\Exception\PendingException;
use Reliese\Generator\DataTransport\DataTransportGenerator;
use Reliese\Generator\DataTransport\DataTransportObjectGenerator;
use Tests\Behat\Contexts\FeatureContext;
/**
 * Class DataTransportObjectGeneratorContext
 */
class DataTransportObjectGeneratorContext extends GeneratorContexts
{
    public function getDataTransportObjectGenerator(): DataTransportObjectGenerator
    {
        return new DataTransportObjectGenerator(
            $this->getConfigurationContexts()
                 ->getDataTransportObjectGeneratorConfigurationContext()
                 ->getDataTransportObjectGeneratorConfiguration()
        );
    }

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