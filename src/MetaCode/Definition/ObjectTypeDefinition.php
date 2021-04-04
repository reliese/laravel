<?php

namespace Reliese\MetaCode\Definition;

class ObjectTypeDefinition implements ImportableInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $typeNamespace;

    /**
     * ObjectTypeDefinition constructor.
     *
     * @param string $fullyQualifiedName
     */
    public function __construct(string $fullyQualifiedName)
    {
        $type = trim($fullyQualifiedName, '\\');
        $pieces = explode('\\', $fullyQualifiedName);

        if (count($pieces) < 1) {
            throw new \RuntimeException('A class name should not be empty');
        }

        $this->name = array_pop($pieces);
        $this->typeNamespace = implode('\\', array_filter($pieces));
    }

    /**
     * @return string
     */
    public function getFullyQualifiedName(): string
    {
        return '\\' . $this->getFullyQualifiedImportableName();
    }

    /**
     * @return string
     */
    public function getFullyQualifiedImportableName(): string
    {
        return $this->getNamespace() . '\\' . $this->getImportableName();
    }

    /**
     * @return string
     */
    public function getImportableName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    private function getNamespace(): string
    {
        return $this->typeNamespace;
    }
}
