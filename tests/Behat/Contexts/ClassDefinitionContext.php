<?php

namespace Tests\Behat\Contexts;

use Reliese\MetaCode\Definition\ClassDefinition;
use Tests\Test;

class ClassDefinitionContext extends FeatureContext
{
    private ?ClassDefinition $classDefinition;

    /**
     * @return ClassDefinition
     */
    public function getClassDefinition(): ClassDefinition
    {
        Test::assertInstanceOf(
            ClassDefinition::class,
            $this->classDefinition,
            // TODO: add message
        );

        return $this->classDefinition;
    }

    /**
     * @param ClassDefinition $classDefinition
     *
     * @return ClassDefinitionContext
     */
    public function setClassDefinition(ClassDefinition $classDefinition): ClassDefinitionContext
    {
        $this->classDefinition = $classDefinition;

        return $this;
    }

    /**
     * @Then /^last ClassDefinition has class name "([^"]*)"$/
     */
    public function lastClassDefinitionHasClassName($className)
    {
        Test::assertEquals(
            $className,
            $this->getClassDefinition()->getClassName()
        );
    }
}
