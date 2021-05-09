<?php

namespace Tests\Behat\Contexts;

use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Tests\Test;

class ClassPropertyDefinitionContext
{
    private ClassDefinition $classDefinition;

    private ClassPropertyDefinition $classPropertyDefinition;

    /**
     * ClassPropertyDefinitionContext constructor.
     */
    public function __construct(ClassDefinition $classDefinition, ClassPropertyDefinition $classPropertyDefinition)
    {
        $this->classDefinition = $classDefinition;
        $this->classPropertyDefinition = $classPropertyDefinition;
    }

    /**
     * @param string $property
     * @param string $message
     *
     * @return $this
     */
    public function thenHasName(string $property, string $message = ''): static
    {
        Test::assertEquals($property, $this->classPropertyDefinition->getVariableName(), $message);
        return $this;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function thenVisibilityIsProtected(string $message = ''): static
    {
        Test::assertTrue($this->classPropertyDefinition->getVisibilityEnum()->isProtected(), $message);
        return $this;

    }

    /**
     * @param string $tableName
     * @param string $message
     *
     * @return $this
     */
    public function thenValueEquals(string $tableName, string $message = ''): static
    {
        Test::assertEquals($tableName, $this->classPropertyDefinition->getValue(), $message);
        return $this;
    }
}
