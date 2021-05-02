<?php

namespace Reliese\Configuration;

/**
 * Class ModelGeneratorConfiguration
 */
class ModelGeneratorConfiguration
{
    /**
     * @var string
     */
    private string $classSuffix;

    /**
     * @var string
     */
    private string $namespace;

    /**
     * @var string
     */
    private string $parentClassPrefix;

    /**
     * @var string
     */
    private string $path;

    /**
     * @var string
     */
    private string $parent;

    /**
     * ModelGeneratorConfiguration constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration = [])
    {
        if (empty($configuration)) {
            return ;
        }

        $this->setPath($configuration['Path']);
        $this->setNamespace($configuration['Namespace']);
        $this->setClassSuffix($configuration['ClassSuffix'] ?? '');
        $this->setParentClassPrefix($configuration['ParentClassPrefix'] ?? '');
        $this->setParent($configuration['Parent']);
    }

    /**
     * @return string
     */
    public function getClassSuffix(): string
    {
        return $this->classSuffix;
    }

    /**
     * @return bool
     */
    public function hasClassSuffix(): bool
    {
        return !empty($this->getClassSuffix());
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
    public function getParentClassPrefix(): string
    {
        return $this->parentClassPrefix;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return ModelGeneratorConfiguration
     */
    public function setPath(string $path): ModelGeneratorConfiguration
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return $this->parent;
    }

    /**
     * @param string $parent
     *
     * @return ModelGeneratorConfiguration
     */
    public function setParent(string $parent): ModelGeneratorConfiguration
    {
        $this->parent = trim($parent, '\\');
        return $this;
    }

    /**
     * @param string $classSuffix
     *
     * @return ModelGeneratorConfiguration
     */
    public function setClassSuffix(mixed $classSuffix): ModelGeneratorConfiguration
    {
        $this->classSuffix = $classSuffix;
        return $this;
    }

    /**
     * @param string $namespace
     *
     * @return ModelGeneratorConfiguration
     */
    public function setNamespace(string $namespace): ModelGeneratorConfiguration
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @param string $parentClassPrefix
     *
     * @return ModelGeneratorConfiguration
     */
    public function setParentClassPrefix(string $parentClassPrefix): ModelGeneratorConfiguration
    {
        $this->parentClassPrefix = $parentClassPrefix;
        return $this;
    }
}
