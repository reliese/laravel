<?php

namespace Reliese\MetaCode\Definition;

/**
 * Class ClassDefinition
 */
class ClassDefinition implements ImportableInterface
{
    /**
     * @var bool[] Array keys are fully qualified interface names
     */
    private array $interfaces = [];

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

    /**
     * @param string $fullyQualifiedInterfaceName
     */
    public function addInterface(string $fullyQualifiedInterfaceName)
    {
        $this->interfaces[$fullyQualifiedInterfaceName] = true;
    }

    public function addMethodDefinition(ClassMethodDefinition $classMethodDefinition)
    {
        $this->methods[$classMethodDefinition->getFunctionName()] = $classMethodDefinition;
    }

    /**
     * @return string[]
     */
    public function getInterfaces(): array
    {
        return \array_keys($this->interfaces);
    }

    public function hasInterfaces(): bool
    {
        return !empty($this->interfaces);
    }

    public function hasTrait(string $fullyQualifiedTraitName)
    {
        if (empty($this->getTraits())) {
            return false;
        }

        foreach ($this->getTraits() as $classTraitDefinition) {
            if ($fullyQualifiedTraitName === $classTraitDefinition->getFullyQualifiedName()) {
                return true;
            }
        }

        return false;
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
        return $this->getName();
    }
}
