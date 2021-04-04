<?php


namespace Reliese\MetaCode\Definition;


class ClassTraitDefinition implements ImportableInterface
{
    private string $name;

    private string $namespace;

    /**
     * ClassTraitDefinition constructor.
     *
     * @param string $name
     * @param string $namespace
     */
    public function __construct(string $name, string $namespace)
    {
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

    public function getFullyQualifiedImportableName(): string
    {
        return trim($this->getFullyQualifiedName(), '\\');
    }

    public function getImportableName(): string
    {
        return $this->getName();
    }
}
