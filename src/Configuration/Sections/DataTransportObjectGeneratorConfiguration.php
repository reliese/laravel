<?php

namespace Reliese\Configuration\Sections;;

use Reliese\Configuration\ClassGeneratorConfigurationInterface;
use Reliese\Configuration\WithClassGeneratorConfigurationMethods;
/**
 * Class DataTransportGeneratorConfiguration
 */
class DataTransportObjectGeneratorConfiguration implements ClassGeneratorConfigurationInterface
{
    use WithClassGeneratorConfigurationMethods;

    /**
     * DataTransportObjectGeneratorConfiguration constructor.
     * @var bool
     */
    private bool $useBeforeChangeObservableProperties = false;

    /**
     * @var bool
     */
    private bool $useAfterChangeObservableProperties = false;

    /**
     * @var bool
     */
    private bool $useValueStateTracking;

    /**
     * DataTransportGeneratorConfiguration constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this
            ->parseClassGeneratorConfiguration($configuration)
            ->setUseValueStateTracking($configuration['UseValueStateTracking'] ?? false)
        ;

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

    /**
     * @return bool
     */
    public function getUseValueStateTracking(): bool
    {
        return $this->useValueStateTracking;
    }

    /**
     * @param bool $useValueStateTracking
     * @return $this
     */
    public function setUseValueStateTracking(bool $useValueStateTracking): static
    {
        $this->useValueStateTracking = $useValueStateTracking;
        return $this;
    }
}
