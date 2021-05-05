<?php

namespace Tests\Behat\Contexts;

use Reliese\Generator\Model\ModelGenerator;
use Reliese\MetaCode\Definition\ClassDefinition;
use Tests\Test;

class ClassDefinitionContext extends FeatureContext
{
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
    public function thenLastClassDefinitionHasClassName($className)
    {
        Test::assertEquals(
            $className,
            $this->getLastClassDefinition()->getClassName()
        );
    }

    /**
     * @Then /^last ClassDefinition file path is "([^"]*)"$/
     */
    public function thenLastClassDefinitionFilePathIs($filePath)
    {
        Test::assertEquals(
            $filePath,
            $this->getLastClassDefinition()->getFilePath()
        );
    }

    /**
     * @Then /^last ClassDefinition namespace is "([^"]*)"$/
     */
    public function thenLastClassDefinitionNamespaceIs($namespace)
    {
        Test::assertEquals(
            $namespace,
            $this->getLastClassDefinition()->getNamespace()
        );
    }

    /**
     * @param string $propertyName
     *
     * @return ClassPropertyDefinitionContext
     */
    public function getLastAbstractClassDefinitionPropertyDefinitionContext(string $propertyName): ClassPropertyDefinitionContext
    {
        $classDefinition = $this->getLastAbstractClassDefinition();

        $message = sprintf('Unable to find property [$%s] on class [%s]',
            $propertyName,
            $classDefinition->getFullyQualifiedName()
        );

        Test::assertTrue(
            $classDefinition->hasProperty($propertyName),
            $message
        );

        $classPropertyDefinition = $classDefinition->getProperty($propertyName);

        return new ClassPropertyDefinitionContext($classDefinition, $classPropertyDefinition);
    }
}
