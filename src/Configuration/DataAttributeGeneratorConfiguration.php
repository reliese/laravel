<?php

namespace Reliese\Configuration;

/**
 * Class DataAttributeGeneratorConfiguration
 */
class DataAttributeGeneratorConfiguration
{
    /**
     * @var mixed
     */
    private $traitPrefix;

    /**
     * @var string
     */
    private string $traitSuffix;

    /**
     * @var string
     */
    private string $namespace;

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
        $this->traitPrefix = $configuration['TraitPrefix'] ?? "";
        $this->traitSuffix = $configuration['TraitSuffix'] ?? "";
    }

    /**
     * @return mixed
     */
    public function getTraitPrefix(): mixed
    {
        return $this->traitPrefix;
    }

    /**
     * @return mixed
     */
    public function getTraitSuffix(): mixed
    {
        return $this->traitSuffix;
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
    public function getPath(): string
    {
        return $this->path;
    }
}
