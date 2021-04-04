<?php

namespace Reliese\MetaCode\Definition;

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
     * @var ClassMethodDefinition[]
     */
    private array $methodDefinitions = [];

    /**
     * @var string
     */
    private string $namespace;

    /**
     * @var ClassConstantDefinition[]
     */
    private array $classConstants = [];

    public function __construct(
        string $className,
        string $namespace
    ) {
        $this->className = $className;
        $this->namespace = trim($namespace, '\\');
    }

    public function addMethodDefinition(ClassMethodDefinition $classMethodDefinition)
    {
        $this->methodDefinitions[$classMethodDefinition->getFunctionName()] = $classMethodDefinition;
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

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function addProperty(ClassPropertyDefinition $classPropertyDefinition): ClassDefinition {
        $this->classProperties[$classPropertyDefinition->getVariableName()] = $classPropertyDefinition;
        return $this;
    }

    /**
     * @return ClassPropertyDefinition[]
     */
    public function getProperties(): array
    {
        return $this->classProperties;
    }

    /**
     * @return ClassMethodDefinition[]
     */
    public function getMethods(): array
    {
        return $this->methodDefinitions;
    }

    public function addConstant(ClassConstantDefinition $oneConstant): static
    {
        $this->classConstants[$oneConstant->getName()] = $oneConstant;
        return $this;
    }

    /**
     * @return ClassConstantDefinition[]
     */
    public function getConstants(): array
    {
        return $this->classConstants;
    }
}
