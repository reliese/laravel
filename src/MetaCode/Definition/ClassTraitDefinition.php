<?php


namespace Reliese\MetaCode\Definition;


use Reliese\MetaCode\Format\IndentationProvider;
use Reliese\MetaCode\Tool\ClassNameTool;
class ClassTraitDefinition implements ImportableInterface
{
    private string $name;

    private string $namespace;

    /**
     * ClassTraitDefinition constructor.
     *
     * @param string $fullyQualifiedTraitName
     */
    public function __construct(string $fullyQualifiedTraitName)
    {
        $this->name = ClassNameTool::fullyQualifiedClassNameToClassName($fullyQualifiedTraitName);
        $this->namespace = trim(ClassNameTool::fullyQualifiedClassNameToNamespace($fullyQualifiedTraitName));
    }

    /**
     * @return string
     */
    public function getTraitName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClassNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getFullyQualifiedName(): string
    {
        return '\\' . implode('\\', array_filter([
            $this->getClassNamespace(),
            $this->getTraitName()
        ]));
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
        return $this->getTraitName();
    }

    /**
     * @param string $traitFullyQualifiedName
     *
     * @return bool
     */
    public function isFullyQualifiedName(string $traitFullyQualifiedName): bool
    {
        $compareTo = '\\' . trim($traitFullyQualifiedName, '\\');

        return $this->getFullyQualifiedName() == $compareTo;
    }

    /**
     * @param IndentationProvider $indentationProvider
     *
     * @return string
     */
    public function toPhpCode(IndentationProvider $indentationProvider)
    {
        return sprintf("%suse %s;", $indentationProvider->getIndentation(), $this->getImportableName());
    }
}
