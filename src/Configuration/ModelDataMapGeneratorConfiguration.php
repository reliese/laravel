<?php

namespace Reliese\Configuration;

/**
 * Class ModelDataMapGeneratorConfiguration
 */
class ModelDataMapGeneratorConfiguration
{
    /**
     * @var mixed|string
     */
    private $accessorTraitNamespace;

    /**
     * @var mixed|string
     */
    private $accessorTraitPath;

    /**
     * @var mixed
     */
    private string $classSuffix;

    /**
     * @var string|mixed
     */
    private string $namespace;

    /**
     * @var mixed
     */
    private string $parentClassPrefix;

    /**
     * @var string|mixed
     */
    private string $path;

    /**
     * DataTransportObjectGeneratorConfiguration constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->path = $configuration['Path'];
        $this->namespace = $configuration['Namespace'];
        $this->classSuffix = $configuration['ClassSuffix'] ?? '';
        $this->parentClassPrefix = $configuration['ParentClassPrefix'] ?? '';
        $this->accessorTraitNamespace = $configuration['AccessorTraitNamespace'] ?? '';
        $this->accessorTraitPath = $configuration['AccessorTraitPath'] ?? '';
    }

    /**
     * @return mixed
     */
    public function getClassSuffix(): mixed
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
     * @return ?string
     */
    public function getAccessorTraitNamespace(): ?string
    {
        return $this->accessorTraitNamespace;
    }

    /**
     * @return mixed|string
     */
    public function getAccessorTraitPath(): mixed
    {
        return $this->accessorTraitPath;
    }
}
