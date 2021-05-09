<?php


namespace Reliese\MetaCode\Definition;


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
        $parts = explode('\\', \ltrim($fullyQualifiedTraitName, '\\'));
        $name = \array_pop($parts);
        $namespace = \implode('\\', $parts);
        $this->name = $name;
        $this->namespace = trim($namespace, '\\');
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
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getFullyQualifiedName(): string
    {
        return '\\' . implode('\\', array_filter([
            $this->getNamespace(),
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
}
