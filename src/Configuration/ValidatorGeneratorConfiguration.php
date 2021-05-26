<?php

namespace Reliese\Configuration;

use Reliese\Filter\SchemaFilter;
/**
 * Class ValidatorGeneratorConfiguration
 */
class ValidatorGeneratorConfiguration
{
    use WithParseSchemaFilters;

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
     * @var mixed
     */
    private string $path;

    /**
     * @var SchemaFilter
     */
    private SchemaFilter $schemaFilter;

    /**
     * DataTransportGeneratorConfiguration constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->path = $configuration['Path'];
        $this->namespace = $configuration['Namespace'];
        $this->classSuffix = $configuration['ClassSuffix'] ?? '';
        $this->parentClassPrefix = $configuration['ParentClassPrefix'] ?? '';
        $this->schemaFilter = $this->parseFilters($configuration);
    }

    /**
     * @return string
     */
    public function getClassSuffix(): mixed
    {
        return $this->classSuffix;
    }

    /**
     * @return string
     */
    public function getNamespace(): mixed
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getParentClassPrefix(): mixed
    {
        return $this->parentClassPrefix;
    }

    /**
     * @return mixed
     */
    public function getPath(): mixed
    {
        return $this->path;
    }

    /**
     * @return SchemaFilter
     */
    public function getSchemaFilter(): SchemaFilter
    {
        return $this->schemaFilter;
    }
}