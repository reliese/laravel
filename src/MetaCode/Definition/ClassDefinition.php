<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Definition\ClassFunctionDefinition;
/**
 * Class GeneratedClass
 */
class ClassDefinition
{
    private string $className;

    /**
     * @var ClassPropertyDefinition[]
     */
    private array $classProperties = [];

    private string $extendedClassName;

    private string $namespace;

    public function __construct(
        string $className,
        string $namespace
    ) {
        $this->className = $className;
        $this->namespace = $namespace;
    }

    public function addFunctionDefinition(ClassFunctionDefinition $classFunctionDefinition)
    {
        $this->functionDefinitions[$classFunctionDefinition->getFunctionName()] = $classFunctionDefinition;
    }

    public function extendsClass(string $fullyQualifiedClassName): static
    {
        $this->extendedClassName = $fullyQualifiedClassName;
        return $this;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getFullyQuallifiedClassName() : string
    {
        return '\\'.$this->getNamespace().'\\'.$this->getClassName();
    }

    private function getNamespace() : string
    {
        return $this->namespace;
    }

    public function addProperty(ClassPropertyDefinition $classPropertyDefinition) : static {
        $this->classProperties[$classPropertyDefinition->getMemberVariableName()] = $classPropertyDefinition;
        return $this;
    }
}
