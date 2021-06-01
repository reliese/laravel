<?php

namespace Reliese\Configuration\Sections;

use Reliese\Configuration\ClassGeneratorConfigurationInterface;
use Reliese\Configuration\WithClassGeneratorConfigurationMethods;
/**
 * Class ModelGeneratorConfiguration
 */
class ModelGeneratorConfiguration implements ClassGeneratorConfigurationInterface
{
    use WithClassGeneratorConfigurationMethods;

    private string $generatedClassParentClass;

    /**
     * ModelGeneratorConfiguration constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this
            ->parseClassGeneratorConfiguration($configuration)
            ->setGeneratedClassParentClass($configuration['GeneratedClassParentClass'])
        ;
    }

    /**
     * @return string
     */
    public function getGeneratedClassParentClass(): string
    {
        return $this->generatedClassParentClass;
    }

    /**
     * @param string $generatedClassParentClass
     *
     * @return ModelGeneratorConfiguration
     */
    public function setGeneratedClassParentClass(string $generatedClassParentClass): ModelGeneratorConfiguration
    {
        $this->generatedClassParentClass = $generatedClassParentClass;
        return $this;
    }
}
