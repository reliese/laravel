<?php

namespace Reliese\Configuration;

/**
 * Class DataTransportGeneratorConfiguration
 */
class DataTransportObjectGeneratorConfiguration
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
     * DataTransportObjectGeneratorConfiguration constructor.
     * @var bool
     */
    private bool $useBeforeChangeObservableProperties = false;

    /**
     * @var bool
     */
    private bool $useAfterChangeObservableProperties = false;

    private $useValueStateTracking;

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
        $this->classSuffix = $configuration['ClassSuffix'];
        $this->parentClassPrefix = $configuration['ParentClassPrefix'];
        $this->useValueStateTracking = $configuration['UseValueStateTracking'] ?? false;
        if (\array_key_exists('ObservableProperties', $configuration)) {
            $observableConfig = $configuration['ObservableProperties'];
            if (\array_key_exists('BeforeChange', $observableConfig)) {
                $this->useBeforeChangeObservableProperties = true === $observableConfig['BeforeChange'];
            }
            if (\array_key_exists('AfterChange', $observableConfig)) {
                $this->useAfterChangeObservableProperties = true === $observableConfig['AfterChange'];
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
    public function getUseAfterChangeObservableProperties(): bool
    {
        return $this->useAfterChangeObservableProperties;
    }

    /**
     * @return bool
     */
    public function getUseBeforeChangeObservableProperties(): bool
    {
        return $this->useBeforeChangeObservableProperties;
    }

    public function getUseValueStateTracking(): bool
    {
        return $this->useValueStateTracking;
    }
}
