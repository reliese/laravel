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

        $this->path = $configuration['Path'];
        $this->namespace = $configuration['Namespace'];
        $this->classSuffix = $configuration['ClassSuffix'];
        $this->parentClassPrefix = $configuration['ParentClassPrefix'];
        $this->parent = $configuration['Parent'];
    }

    /**
     * @return mixed
     */
    public function getClassSuffix(): string
    {
        return $this->classSuffix;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return mixed
     */
    public function getParentClassPrefix(): mixed
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
     * @return string
     */
    public function getParent(): string
    {
        return $this->parent;
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
    public function setNamespace(mixed $namespace): ModelGeneratorConfiguration
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @param string $parentClassPrefix
     *
     * @return ModelGeneratorConfiguration
     */
    public function setParentClassPrefix(mixed $parentClassPrefix): ModelGeneratorConfiguration
    {
        $this->parentClassPrefix = $parentClassPrefix;
        return $this;
    }

    /**
     * @param string $path
     *
     * @return ModelGeneratorConfiguration
     */
    public function setPath(mixed $path): ModelGeneratorConfiguration
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param string $parent
     *
     * @return ModelGeneratorConfiguration
     */
    public function setParent(mixed $parent): ModelGeneratorConfiguration
    {
        $this->parent = $parent;
        return $this;
    }
}
