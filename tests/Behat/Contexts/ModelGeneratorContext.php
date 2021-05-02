<?php

namespace Tests\Behat\Contexts;

use Reliese\Generator\Model\ModelGenerator;
use Tests\Test;

class ModelGeneratorContext extends FeatureContext
{
    private ?ModelGenerator $modelGenerator;

    /**
     * @return ModelGenerator
     */
    public function getModelGenerator(): ModelGenerator
    {
        Test::assertInstanceOf(ModelGenerator::class, $this->modelGenerator);

        return $this->modelGenerator;
    }

    /**
     * @Given /^a ModelGenerator is created$/
     */
    public function aModelGeneratorIsCreated()
    {
        $this->modelGenerator = new ModelGenerator(
            $this->modelGeneratorConfigurationContext->getModelGeneratorConfiguration()
        );
    }

    /**
     * @Then /^ModelGenerator class directory is "([^"]*)"$/
     */
    public function thenModelGeneratorClassDirectoryIs($path)
    {
        Test::assertEquals($path, $this->getModelGenerator()->getClassDirectory());
    }

    /**
     * @When /^a Model ClassDefinition is generated$/
     */
    public function aModelClassDefinitionIsGenerated()
    {
        $modelClassDefinition = $this->getModelGenerator()->generateModelClass(
            $this->tableBlueprintContext->getLastTableBlueprint()
        );

        $this->classDefinitionContext->setLastClassDefinition($modelClassDefinition);

        $modelAbstractClassDefinition = $this->getModelGenerator()->generateModelAbstractClass(
            $this->tableBlueprintContext->getLastTableBlueprint()
        );

        $this->classDefinitionContext->setLastAbstractClassDefinition($modelAbstractClassDefinition);
    }
}