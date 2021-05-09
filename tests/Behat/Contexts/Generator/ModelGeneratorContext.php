<?php

namespace Tests\Behat\Contexts\Generator;

use Reliese\Generator\Model\ModelGenerator;
use Tests\Test;

class ModelGeneratorContext extends GeneratorContexts
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
    public function givenAModelGeneratorIsCreated()
    {
        $this->modelGenerator = new ModelGenerator(
            $this->getConfigurationContexts()
                 ->getModelGeneratorConfigurationContext()
                 ->getModelGeneratorConfiguration()
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
    public function whenAModelClassDefinitionIsGenerated()
    {
        $this->classDefinitionContext->setLastClassDefinition(
            $this->getModelGenerator()
                 ->generateModelClass(
                     $this->getBlueprintContexts()->getTableBlueprintContext()->getLastTableBlueprint()
                 )
        );
    }

    /**
     * @When /^an Abstract Model ClassDefinition is generated$/
     */
    public function whenAnAbstractModelClassDefinitionIsGenerated()
    {
        $this->classDefinitionContext->setLastAbstractClassDefinition(
            $this->getModelGenerator()
                 ->generateModelAbstractClass(
                     $this->getBlueprintContexts()->getTableBlueprintContext()->getLastTableBlueprint()
                 )
        );
    }

    /**
     * @Then /^last Model ClassDefinition file path is "([^"]*)"$/
     */
    public function thenLastModelClassDefinitionFilePathIs($filePath)
    {
        $this->classDefinitionContext->thenLastClassDefinitionFilePathIs($filePath);
    }

    /**
     * @Then /^last Model ClassDefinition namespace is "([^"]*)"$/
     */
    public function thenLastModelClassDefinitionNamespaceIs($namespace)
    {
        $this->classDefinitionContext->thenLastClassDefinitionNamespaceIs($namespace);
    }

    /**
     * @Then /^last Model ClassDefinition has class name "([^"]*)"$/
     */
    public function thenLastModelClassDefinitionHasClassName($className)
    {
        $this->classDefinitionContext->thenLastClassDefinitionHasClassName($className);
    }

    /**
     * @Then /^last Abstract Model ClassDefinition has trait "([^"]*)"$/
     */
    public function thenLastModelClassDefinitionHasTrait($trait)
    {
        $this->classDefinitionContext->thenLastAbstractClassDefinitionHasTrait($trait);
    }

    /**
     * @Then /^last Abstract Model ClassDefinition has Eloquent table property with value "([^"]*)"$/
     */
    public function thenLastAbstractModelClassDefinitionHasEloquentTablePropertyWithValue($tableName)
    {
        $classPropertyDefinitionContext = $this->classDefinitionContext
            ->getLastAbstractClassDefinitionPropertyDefinitionContext(ModelGenerator::PROPERTY_TABLE);

        $classPropertyDefinitionContext->thenHasName(
            ModelGenerator::PROPERTY_TABLE,
            'Eloquent Model property $table must be present'
        );

        $classPropertyDefinitionContext->thenVisibilityIsProtected(
            'Eloquent Model property $table must be private'
        );

        $classPropertyDefinitionContext->thenValueEquals(
            $tableName,
            "Eloquent Model property \$table must have value equals to [$tableName]"
        );
    }

    /**
     * @Then /^last Abstract Model ClassDefinition must not have Eloquent table property$/
     */
    public function thenLastAbstractModelClassDefinitionMustNotHaveEloquentTableProperty()
    {
        $classDefinition = $this->classDefinitionContext->getLastAbstractClassDefinition();

        $hasProperty = $classDefinition->hasProperty(ModelGenerator::PROPERTY_TABLE);

        Test::assertFalse(
            $hasProperty,
            'Eloquent Model property $table must not be present'
        );
    }

    /**
     * @Then /^last Abstract Model ClassDefinition extends from "([^"]*)"$/
     */
    public function lastAbstractModelClassDefinitionExtendsFrom($parentClassName)
    {
        $classDefinition = $this->classDefinitionContext->getLastAbstractClassDefinition();

        Test::assertEquals(
            $parentClassName,
            $classDefinition->getParentClassName(),
            "Abstract Model should extend from [$parentClassName]"
        );
    }

    /**
     * @Then /^last Abstract Model ClassDefinition class name is "([^"]*)"$/
     */
    public function lastAbstractModelClassDefinitionClassNameIs($className)
    {
        $classDefinition = $this->classDefinitionContext->getLastAbstractClassDefinition();

        Test::assertEquals(
            $className,
            $classDefinition->getClassName(),
            "Abstract Model must be named [$className]"
        );
    }
}