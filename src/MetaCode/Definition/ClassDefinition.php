<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Definition\ClassFunctionDefinition;
/**
 * Class ClassDefinition
 */
class ClassDefinition
{
    private string $className;

    /**
     * @var ClassPropertyDefinition[]
     */
    private array $classProperties = [];

    /**
     * @var string
     */
    private string $extendedClassName;

    /**
     * @var FunctionDefinition[]
     */
    private array $functionDefinitions;

    /**
     * @var string
     */
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

    public function extendsClass(string $fullyQualifiedClassName): ClassDefinition
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

    public function addProperty(ClassPropertyDefinition $classPropertyDefinition): ClassDefinition {
        $this->classProperties[$classPropertyDefinition->getVariableName()] = $classPropertyDefinition;
        return $this;
    }
}
