<?php

namespace Reliese\MetaCode\Definition;

use Reliese\MetaCode\Definition\CodeDefinitionInterface;
/**
 * Class ClassDefinition
 */
class ClassDefinition implements ImportableInterface, CodeDefinitionInterface
{
    private string $name;

    /**
     * @var string
     */
    private string $namespace;

    /**
     * @var string|null
     */
    private ?string $parentClassName = null;

    private string $directory;

    private string $filePath;

    /**
     * @param string $filePath
     *
     * @return ClassDefinition
     */
    public function setFilePath(string $filePath): ClassDefinition
    {
        $this->filePath = $filePath;
        return $this;
    }

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

    public function setParentClass(string $fullyQualifiedClassName): ClassDefinition
    {
        $this->parentClassName = $fullyQualifiedClassName;
        return $this;
    }

    public function hasParentClass(): bool
    {
        return !is_null($this->parentClassName);
    }

    public function getParentClassName() : string
    {
        return $this->parentClassName;
    }

    public function getClassName(): string
    {
        return $this->name;
    }

    public function getFullyQualifiedName(): string
    {
        return '\\'.$this->getNamespace().'\\'.$this->getClassName();
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
            // Will collide when namespaces are different
            return !$this->areSameImport($import, $this);
        }

        // Won't collide when not previously imported
        if (array_key_exists($import->getImportableName(), $this->imports) === false) {
            return false;
        }

        $imported = $this->imports[$import->getImportableName()];

        // Will collide when they are not the same class
        return !$this->areSameImport($import, $imported);
    }

    /**
     * @param ClassConstantDefinition[] $constants
     *
     * @return $this
     */
    public function addConstants(array $constants): static
    {
        foreach ($constants as $constant) {
            $this->addConstant($constant);
        }

        return $this;
    }

    /**
     * @todo: Put this on a helper class
     *
     * @param ImportableInterface $importing
     * @param ImportableInterface $imported
     *
     * @return bool
     */
    private function areSameImport(ImportableInterface $importing, ImportableInterface $imported): bool
    {
        return strcmp($importing->getFullyQualifiedImportableName(), $imported->getFullyQualifiedImportableName()) === 0;
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
        return $this->getClassName();
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * @param string $directory
     *
     * @return ClassDefinition
     */
    public function setDirectory(string $directory): ClassDefinition
    {
        $this->directory = $directory;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
