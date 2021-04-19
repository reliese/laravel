<?php

namespace Reliese\MetaCode\Definition;

/**
 * Class ClassDefinition
 */
class ClassDefinition implements ImportableInterface
{
    /**
     * @var string[]
     */
    private array $classComments = [];

    /**
     * @var ClassConstantDefinition[]
     */
    private array $constants = [];

    /**
     * @var ImportableInterface[]
     */
    private array $imports = [];

    /**
     * @var ClassMethodDefinition[]
     */
    private array $methods = [];

    private string $name;

    /**
     * @var string
     */
    private string $namespace;

    /**
     * @var string|null
     */
    private ?string $parentClassName = null;

    /**
     * @var ClassPropertyDefinition[]
     */
    private array $properties = [];

    /**
     * @var ClassTraitDefinition[]
     */
    private array $traits = [];

    /**
     * ClassDefinition constructor.
     *
     * @param string $name
     * @param string $namespace
     */
    public function __construct(string $name,
        string $namespace)
    {
        $this->name = $name;
        $this->namespace = trim($namespace, '\\');
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function addClassComment(string $comment): static
    {
        $this->classComments[] = $comment;
        return $this;
    }

    /**
     * @param ClassConstantDefinition $constant
     *
     * @return $this
     */
    public function addConstant(ClassConstantDefinition $constant): static
    {
        $this->constants[$constant->getName()] = $constant;
        return $this;
    }

    /**
     * @param ImportableInterface $import
     *
     * @return $this
     */
    public function addImport(ImportableInterface $import): static
    {
        // We'll assume this class is already imported to shorten references to itself
        if (strcmp($import->getFullyQualifiedImportableName(), $this->getFullyQualifiedImportableName()) === 0) {
            return $this;
        }

        $this->imports[$import->getImportableName()] = $import;
        return $this;
    }

    /**
     * @param ClassMethodDefinition $classMethodDefinition
     */
    public function addMethodDefinition(ClassMethodDefinition $classMethodDefinition)
    {
        $this->methods[$classMethodDefinition->getFunctionName()] = $classMethodDefinition;
    }

    /**
     * @param ClassPropertyDefinition $classPropertyDefinition
     *
     * @return $this
     */
    public function addProperty(ClassPropertyDefinition $classPropertyDefinition): ClassDefinition
    {
        $this->properties[$classPropertyDefinition->getVariableName()] = $classPropertyDefinition;
        return $this;
    }

    public function addTrait(ClassTraitDefinition $trait): static
    {
        $this->traits[$trait->getName()] = $trait;
        return $this;
    }

    public function getClassComments(): array
    {
        return $this->classComments;
    }

    /**
     * @return ClassConstantDefinition[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    public function getFullyQualifiedImportableName(): string
    {
        return trim($this->getFullyQualifiedName(), '\\');
    }

    public function getFullyQualifiedName(): string
    {
        return '\\' . $this->getNamespace() . '\\' . $this->getName();
    }

    public function getImportableName(): string
    {
        return $this->getName();
    }

    /**
     * @return ImportableInterface[]
     */
    public function getImports(): array
    {
        return $this->imports;
    }

    /**
     * @return ClassMethodDefinition[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getParentClassName(): string
    {
        return $this->parentClassName;
    }

    /**
     * @return ClassPropertyDefinition[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return string
     */
    public function getStructureType(): string
    {
        return 'class';
    }

    /**
     * @return ClassTraitDefinition[]
     */
    public function getTraits(): array
    {
        return $this->traits;
    }

    public function hasParentClass(): bool
    {
        return !is_null($this->parentClassName);
    }

    public function setParentClass(string $fullyQualifiedClassName): ClassDefinition
    {
        $this->parentClassName = $fullyQualifiedClassName;
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
}
