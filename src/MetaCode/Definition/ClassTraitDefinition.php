<?php


namespace Reliese\MetaCode\Definition;


class ClassTraitDefinition implements ImportableInterface
{
    private string $name;

    private string $namespace;

    /**
     * ClassTraitDefinition constructor.
     *
     * @param string $fullyQuallifiedTraitName
     */
    public function __construct(string $fullyQuallifiedTraitName)
    {
        $parts = explode('\\', \ltrim($fullyQuallifiedTraitName, '\\'));
        $name = \array_pop($parts);
        $namespace = \implode('\\', $parts);
        $this->name = $name;
        $this->namespace = trim($namespace, '\\');
    }

    /**
     * @return string
     */
    public function getName(): string
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
        return '\\' . $this->getNamespace() . '\\' . $this->getName();
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
        return $this->getName();
    }
}
