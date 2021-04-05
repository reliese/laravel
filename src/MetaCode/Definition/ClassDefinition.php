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
     * @var string|null
     */
    private ?string $baseClassName = null;

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

    public function setBaseClass(string $fullyQualifiedClassName): ClassDefinition
    {
        $this->baseClassName = $fullyQualifiedClassName;
        return $this;
    }

    public function hasBaseClass(): bool
    {
        return !\is_null($this->baseClassName);
    }

    public function getBaseClassName() : string
    {
        return $this->baseClassName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullyQualifiedName(): string
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
        // We'll assume this class is already imported to shorten references to itself
        if (strcmp($import->getFullyQualifiedImportableName(), $this->getFullyQualifiedImportableName()) === 0) {
            return $this;
        }

        $this->imports[$import->getImportableName()] = $import;
        return $this;
    }

    public function willCollideImport(ImportableInterface $import): bool
    {
        // When it's the same class name
        if (strcmp($import->getImportableName(), $this->getImportableName()) === 0) {
            // And it's same namespace as this one
            if (strcmp($import->getFullyQualifiedImportableName(), $this->getFullyQualifiedImportableName()) === 0) {
                // The import won't have a collision
                return false;
            }

            // Will collide when different namespace
            return true;
        }

        return array_key_exists($import->getImportableName(), $this->imports);
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
        return trim($this->getFullyQualifiedName(), '\\');
    }

    public function getImportableName(): string
    {
        return $this->getName();
    }
}
