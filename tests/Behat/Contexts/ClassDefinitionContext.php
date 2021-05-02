<?php

namespace Tests\Behat\Contexts;

use Reliese\MetaCode\Definition\ClassDefinition;
use Tests\Test;

class ClassDefinitionContext extends FeatureContext
{
    const ELOQUENT_PROPERTY_TABLE_NAME = 'table';
    const ELOQUENT_PROPERTY_TABLE_VISIBILITY = 'protected';

    private ?ClassDefinition $lastClassDefinition;
    private ?ClassDefinition $lastAbstractClassDefinition;

    /**
     * @return ClassDefinition
     */
    public function getLastClassDefinition(): ClassDefinition
    {
        Test::assertInstanceOf(
            ClassDefinition::class,
            $this->lastClassDefinition,
            // TODO: add message
        );

        return $this->lastClassDefinition;
    }

    /**
     * @return ClassDefinition
     */
    public function getLastAbstractClassDefinition(): ClassDefinition
    {
        Test::assertInstanceOf(
            ClassDefinition::class,
            $this->lastAbstractClassDefinition,
            // TODO: add message
        );

        return $this->lastAbstractClassDefinition;
    }

    /**
     * @param ClassDefinition $lastClassDefinition
     *
     * @return ClassDefinitionContext
     */
    public function setLastClassDefinition(ClassDefinition $lastClassDefinition): ClassDefinitionContext
    {
        $this->lastClassDefinition = $lastClassDefinition;
        return $this;
    }

    /**
     * @param ClassDefinition $lastAbstractClassDefinition
     *
     * @return ClassDefinitionContext
     */
    public function setLastAbstractClassDefinition(ClassDefinition $lastAbstractClassDefinition): ClassDefinitionContext
    {
        $this->lastAbstractClassDefinition = $lastAbstractClassDefinition;
        return $this;
    }

    /**
     * @Then /^last ClassDefinition has class name "([^"]*)"$/
     */
    public function lastClassDefinitionHasClassName($className)
    {
        Test::assertEquals(
            $className,
            $this->getLastClassDefinition()->getClassName()
        );
    }

    /**
     * @Then /^last ClassDefinition file path is "([^"]*)"$/
     */
    public function lastClassDefinitionFilePathIs($filePath)
    {
        Test::assertEquals(
            $filePath,
            $this->getLastClassDefinition()->getFilePath()
        );
    }

    /**
     * @Then /^last AbstractClassDefinition has Eloquent table property with value "([^"]*)"$/
     */
    public function lastAbstractClassDefinitionHasEloquentTablePropertyWithValue($tableName)
    {
        $classDefinition = $this->getLastAbstractClassDefinition();

        $hasProperty = $classDefinition->hasProperty(static::ELOQUENT_PROPERTY_TABLE_NAME);

        Test::assertTrue(
            $hasProperty,
            'Eloquent Model property $table must be present'
        );

        $propertyDefinition = $classDefinition->getProperty(static::ELOQUENT_PROPERTY_TABLE_NAME);

        Test::assertEquals(
            static::ELOQUENT_PROPERTY_TABLE_NAME,
            $propertyDefinition->getVariableName(),
            'Eloquent Model property $table must have table as name'
        );

        Test::assertEquals(
            $tableName,
            $propertyDefinition->getValue(),
            "Eloquent Model property \$table must have value equals to [$tableName]"
        );
    }

    /**
     * @Then /^last AbstractClassDefinition must not have Eloquent table property$/
     */
    public function lastAbstractClassDefinitionMustNotHaveEloquentTableProperty()
    {
        $classDefinition = $this->getLastAbstractClassDefinition();

        $hasProperty = $classDefinition->hasProperty(static::ELOQUENT_PROPERTY_TABLE_NAME);

        Test::assertFalse(
            $hasProperty,
            'Eloquent Model property $table must not be present'
        );
    }

    /**
     * @Then /^last AbstractClassDefinition extends from "([^"]*)"$/
     */
    public function lastAbstractClassDefinitionExtendsFrom($parentClassName)
    {
        $classDefinition = $this->getLastAbstractClassDefinition();

        Test::assertEquals(
            $parentClassName,
            $classDefinition->getParentClassName(),
            "Abstract Model should extend from [$parentClassName]"
        );
    }
}
