<?php

namespace Reliese\Configuration;

/**
 * Class DataAccessGeneratorConfiguration
 */
class DataAccessGeneratorConfiguration
{
    /**
     * @var mixed|string
     */
    private $classPrefix;

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
     * DataTransportObjectGeneratorConfiguration constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->path = $configuration['Path'];
        $this->namespace = $configuration['Namespace'];
        $this->classPrefix = $configuration['ClassPrefix'] ?? '';
        $this->classSuffix = $configuration['ClassSuffix'] ?? '';
        $this->parentClassPrefix = $configuration['ParentClassPrefix'] ?? '';
    }

    /**
     * @return mixed|string
     */
    public function getClassPrefix(): mixed
    {
        return $this->classPrefix;
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
}
