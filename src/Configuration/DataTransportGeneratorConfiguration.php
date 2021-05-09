<?php

namespace Reliese\Configuration;

/**
 * Class DataTransportGeneratorConfiguration
 *
 * @deprecated Please use DataTransportObjectGeneratorConfiguration
 */
class DataTransportGeneratorConfiguration
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
     * @var bool
     */
    private bool $useBeforeChangeObservableProperties = false;

    /**
     * @var bool
     */
    private bool $useAfterChangeObservableProperties = false;

    /**
     * DataTransportGeneratorConfiguration constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->path = $configuration['Path'];
        $this->namespace = $configuration['Namespace'];
        $this->classSuffix = $configuration['ClassSuffix'];
        $this->parentClassPrefix = $configuration['ParentClassPrefix'];
        if (\array_key_exists('ObservableProperties', $configuration)) {
            $observableConfig = $configuration['ObservableProperties'];
            if (\array_key_exists('BeforeChange', $observableConfig)) {
                $this->useBeforeChangeObservableProperties = $observableConfig['BeforeChange'];
            }
            if (\array_key_exists('AfterChange', $observableConfig)) {
                $this->useBeforeChangeObservableProperties = $observableConfig['AfterChange'];
            }
        }
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
     * @return bool
     */
    public function useAfterChangeObservableProperties(): bool
    {
        return $this->useAfterChangeObservableProperties;
    }

    /**
     * @return bool
     */
    public function useBeforeChangeObservableProperties(): bool
    {
        return $this->useBeforeChangeObservableProperties;
    }
}
