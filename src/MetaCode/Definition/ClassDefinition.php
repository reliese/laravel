<?php

namespace Reliese\MetaCode\Definition;

use RuntimeException;

/**
 * Class ClassDefinition
 */
class ClassDefinition implements ImportableInterface, CodeDefinitionInterface
{
    /**
     * @var string
     */
    private string $className;

    /**
     * @var string
     */
    private string $namespace;

    /**
     * @var string|null
     */
    private ?string $parentClassName = null;

    /**
     * @var string
     */
    private string $directory;

    /**
     * @var string
     */
    private string $filePath;

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

    /**
     * ClassDefinition constructor.
     *
     * @param string $name
     * @param string $namespace
     */
    public function __construct(
        string $name,
        string $namespace
    ) {
        $this->className = $name;
        $this->namespace = trim($namespace, '\\');
    }

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
     * @param ClassMethodDefinition $classMethodDefinition
     */
    public function addMethodDefinition(ClassMethodDefinition $classMethodDefinition)
    {
        $this->methods[$classMethodDefinition->getFunctionName()] = $classMethodDefinition;
    }

    /**
     * @param string $fullyQualifiedClassName
     *
     * @return $this
     */
    public function setParentClass(string $fullyQualifiedClassName): ClassDefinition
    {
        $this->parentClassName = $fullyQualifiedClassName;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasParentClass(): bool
    {
        return !is_null($this->parentClassName);
    }

    /**
     * @return string
     */
    public function getParentClassName() : string
    {
        return $this->parentClassName;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getFullyQualifiedName(): string
    {
        return '\\'.$this->getNamespace().'\\'.$this->getClassName();
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
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

    /**
     * @param ClassPropertyDefinition[] $classPropertyDefinitions
     *
     * @return $this
     */
    public function addProperties(array $classPropertyDefinitions): ClassDefinition
    {
        foreach ($classPropertyDefinitions as $classPropertyDefinition) {
            $this->addProperty($classPropertyDefinition);
        }

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
     * @param string $propertyName
     *
     * @return bool
     */
    public function hasProperty(string $propertyName): bool
    {
        return array_key_exists($propertyName, $this->getProperties());
    }

    /**
     * @param string $propertyName
     *
     * @return ClassPropertyDefinition
     */
    public function getProperty(string $propertyName): ClassPropertyDefinition
    {
        if (!$this->hasProperty($propertyName)) {
            throw new RuntimeException("Calling unregistered property [$propertyName] from [{$this->getClassName()}]");
        }

        return $this->getProperties()[$propertyName];
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

    /**
     * @param ClassTraitDefinition $trait
     *
     * @return $this
     */
    public function addTrait(ClassTraitDefinition $trait): static
    {
        $this->traits[$trait->getName()] = $trait;
        return $this;
    }

    /**
     * @param ClassTraitDefinition[] $traitDefinitions
     *
     * @return $this
     */
    public function addTraits(array $traitDefinitions): static
    {
        foreach ($traitDefinitions as $traitDefinition) {
            $this->addTrait($traitDefinition);
        }

        return $this;
    }

    /**
     * @return ClassTraitDefinition[]
     */
    public function getTraits(): array
    {
        return $this->traits;
    }

    /**
     * @param string $fullyQualifiedTraitName
     *
     * @return bool
     */
    public function hasTrait(string $fullyQualifiedTraitName): bool
    {
        foreach ($this->getTraits() as $classTraitDefinition) {
            if ($classTraitDefinition->isFullyQualifiedName($fullyQualifiedTraitName)) {
                return true;
            }
        }

        return false;
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
     * @param ImportableInterface $import
     *
     * @return bool
     */
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

    /**
     * @return string
     */
    public function getFullyQualifiedImportableName(): string
    {
        return trim($this->getFullyQualifiedName(), '\\');
    }

    /**
     * @return string
     */
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
