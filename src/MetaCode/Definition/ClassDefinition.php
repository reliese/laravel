<?php

namespace Reliese\MetaCode\Definition;

/**
 * Class ClassDefinition
 */
class ClassDefinition implements ImportableInterface
{
    private string $name;

    /**
     * @var string
     */
    private string $namespace;

    /**
     * @var string
     */
    private string $extendedClassName;

    /**
     * @var ImportableInterface[]
     */
    private array $imports = [];

    /**
     * @var ClassTraitDefinition[]
     */
    private array $traits = [];

    /**
     * @var ClassConstantDefinition[]
     */
    private array $constants = [];

    /**
     * @var ClassPropertyDefinition[]
     */
    private array $properties = [];

    /**
     * @var ClassMethodDefinition[]
     */
    private array $methods = [];

    public function __construct(
        string $name,
        string $namespace
    ) {
        $this->name = $name;
        $this->namespace = trim($namespace, '\\');
    }

    public function addMethodDefinition(ClassMethodDefinition $classMethodDefinition)
    {
        $this->methods[$classMethodDefinition->getFunctionName()] = $classMethodDefinition;
    }

    public function extendsClass(string $fullyQualifiedClassName): ClassDefinition
    {
        $this->extendedClassName = $fullyQualifiedClassName;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullyQualifiedClassName(): string
    {
        return '\\'.$this->getNamespace().'\\'.$this->getName();
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function addProperty(ClassPropertyDefinition $classPropertyDefinition): ClassDefinition {
        $this->properties[$classPropertyDefinition->getVariableName()] = $classPropertyDefinition;
        return $this;
    }

    /**
     * @return ClassPropertyDefinition[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return ClassMethodDefinition[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function addConstant(ClassConstantDefinition $constant): static
    {
        $this->constants[$constant->getName()] = $constant;
        return $this;
    }

    /**
     * @return ClassConstantDefinition[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    public function addTrait(ClassTraitDefinition $trait): static
    {
        $this->traits[$trait->getName()] = $trait;
        return $this;
    }

    /**
     * @return ClassTraitDefinition[]
     */
    public function getTraits(): array
    {
        return $this->traits;
    }

    public function addImport(ImportableInterface $import): static
    {
        $this->imports[$import->getImportableName()] = $import;
        return $this;
    }

    public function willCollideImport(ImportableInterface $import): bool
    {
        if (strcmp($import->getImportableName(), $this->getImportableName()) == 0) {
            return true;
        }

        if (!array_key_exists($import->getImportableName(), $this->imports)) {
            return false;
        }

        return strcmp($import->getImportableName(), $this->getImportableName()) === 0;
    }

    /**
     * @return ImportableInterface[]
     */
    public function getImports(): array
    {
        return $this->imports;
    }

    public function getFullyQualifiedImportableName(): string
    {
        return trim($this->getFullyQualifiedClassName(), '\\');
    }

    public function getImportableName(): string
    {
        return $this->getName();
    }
}
