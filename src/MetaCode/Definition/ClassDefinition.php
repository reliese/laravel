<?php

namespace Reliese\MetaCode\Definition;

use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Blueprint\TableBlueprint;
use Reliese\MetaCode\Enum\AbstractEnum;
use Reliese\MetaCode\Format\ClassFormatter;
use Reliese\MetaCode\Format\IndentationProvider;
use Reliese\MetaCode\Tool\ClassNameTool;
use RuntimeException;
use function debug_backtrace;
use function PHPUnit\Framework\stringContains;
use function strpos;

/**
 * Class ClassDefinition
 */
class ClassDefinition implements ImportableInterface, CodeDefinitionInterface, StatementDefinitionInterface
{
    /**
     * @var ObjectTypeDefinition
     */
    protected ObjectTypeDefinition $objectTypeDefinition;

    private ?TableBlueprint $originatingBlueprint = null;

    /**
     * @var AbstractEnum
     */
    private ?AbstractEnum $abstractEnumType;

    /**
     * @var bool[] Array keys are fully qualified interface names
     */
    private array $interfaces = [];

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

    /**
     * @var string
     */
    private string $namespace;

    /**
     * @var string|null
     */
    private ?string $parentClassName = null;

    private ?ObjectTypeDefinition $parentObjectTypeDefinition = null;

    /**
     * @var string
     */
    private string $directory;

    /**
     * @var string
     */
    private string $filePath;

    /**
     * @var ClassPropertyDefinition[]
     */
    private array $properties = [];

    /**
     * @var ClassTraitDefinition[]
     */
    private array $traits = [];

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
     * ClassDefinition constructor.
     *
     * @param ObjectTypeDefinition $objectTypeDefinition
     * @param ?AbstractEnum        $abstractEnumType
     */
    public function __construct(
        ObjectTypeDefinition $objectTypeDefinition,
        ?AbstractEnum $abstractEnumType = null
    ) {
        $this->constructorStatementsCollection = new StatementDefinitionCollection();
        $this->abstractEnumType = $abstractEnumType ?? AbstractEnum::concreteEnum();
        $this->objectTypeDefinition = $objectTypeDefinition;
    }

    /**
     * @param string $fullyQualifiedInterfaceName
     *
     * @return $this
     */
    public function addInterface(string $fullyQualifiedInterfaceName): static
    {
        $fullyQualifiedInterfaceName = ClassNameTool::globalClassFQN($fullyQualifiedInterfaceName);
        $this->interfaces[$fullyQualifiedInterfaceName] = true;
        return $this;
    }

    /*
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
     *
     * @return $this
     */
    public function addMethodDefinition(ClassMethodDefinition $classMethodDefinition) : static
    {
        $this->methods[$classMethodDefinition->getFunctionName()] = $classMethodDefinition;
        return $this;
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

    /**
     * @param ClassPropertyDefinition $classPropertyDefinition
     *
     * @return $this
     */
    public function addProperty(ClassPropertyDefinition $classPropertyDefinition): ClassDefinition
    {
        $this->properties[$classPropertyDefinition->getVariableName()] = $classPropertyDefinition;

        if ($classPropertyDefinition->getPhpTypeEnum()->isImportable()) {
            $this->addImport(
                $classPropertyDefinition->getPhpTypeEnum()->toObjectTypeDefinition()
            );
        }

        if ($classPropertyDefinition->getIsBeforeChangeObservable()
            || $classPropertyDefinition->getIsAfterChangeObservable()) {

            $this->addConstant(
                new ClassConstantDefinition(
                    ClassPropertyDefinition::getPropertyNameConstantName(
                        $classPropertyDefinition->getVariableName()
                    ),
                    $classPropertyDefinition->getVariableName()
                )
            );
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasParentClass(): bool
    {
        return $this->parentObjectTypeDefinition instanceof ObjectTypeDefinition;
    }

    public function getClassComments(): array
    {
        return $this->classComments;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->objectTypeDefinition->getImportableName();
    }

    /**
     * @deprecated use getClassName instead
     * @return string
     */
    public function getName(): string { return $this->getClassName(); }

    /**
     * @return string
     */
    public function getFullyQualifiedName(): string
    {
        return '\\'.$this->getClassNamespace().'\\'.$this->getClassName();
    }

    /**
     * @return string
     */
    public function getClassNamespace(): string
    {
        return $this->objectTypeDefinition->getClassNamespace();
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

    public function getParentClassName(): string
    {
        return $this->parentObjectTypeDefinition->getImportableName();
    }

    /**
     * @return ObjectTypeDefinition|null
     */
    public function getParentObjectTypeDefinition(): ?ObjectTypeDefinition
    {
        return $this->parentObjectTypeDefinition;
    }

    /**
     * @return ClassPropertyDefinition[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param ClassTraitDefinition $trait
     *
     * @return $this
     */
    public function addTrait(ClassTraitDefinition $trait): static
    {
        $this->traits[$trait->getTraitName()] = $trait;
        $this->addImport($trait);
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

    public function setParentClass(ObjectTypeDefinition|string $value): ClassDefinition
    {
        if (!($value instanceof ObjectTypeDefinition)) {
            $value = new ObjectTypeDefinition($value);;
        }

        $this->parentObjectTypeDefinition = $value;
        $this->addImport($value);

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

    private StatementDefinitionCollection $constructorStatementsCollection;

    public function getConstructorStatementsCollection(): StatementDefinitionCollection
    {
        return $this->constructorStatementsCollection;
    }

    public function addConstructorStatement(StatementDefinitionInterface $statementDefinition): static
    {
        $this->getConstructorStatementsCollection()
            ->addStatementDefinition($statementDefinition);
        return $this;
    }

    /**
     * @param ColumnOwnerInterface|null $originatingBlueprint
     *
     * @return ClassDefinition
     */
    public function setOriginatingBlueprint(?ColumnOwnerInterface $originatingBlueprint): ClassDefinition
    {
        $this->originatingBlueprint = $originatingBlueprint;
        return $this;
    }

    /**
     * @return ColumnOwnerInterface|null
     */
    public function getOriginatingBlueprint(): ?ColumnOwnerInterface
    {
        return $this->originatingBlueprint;
    }

    public function hasOriginatingBlueprint(): bool
    {
        return $this->originatingBlueprint instanceof TableBlueprint;
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

    /**
     * @return AbstractEnum
     */
    public function getAbstractEnumType(): AbstractEnum
    {
        return $this->abstractEnumType;
    }

    public function toPhpCode(IndentationProvider $indentationProvider): string
    {
        return (new ClassFormatter($indentationProvider))->format($this);
    }
}
