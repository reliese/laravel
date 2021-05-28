<?php

namespace Reliese\Configuration\Sections;;

use Reliese\Configuration\AccessorTraitGeneratorConfigurationInterface;
use Reliese\Configuration\ClassGeneratorConfigurationInterface;
use Reliese\Configuration\WithAccessorTraitGeneratorConfigurationMethods;
use Reliese\Configuration\WithClassGeneratorConfigurationMethods;
/**
 * Class ModelDataMapGeneratorConfiguration
 */
class ModelDataMapGeneratorConfiguration implements ClassGeneratorConfigurationInterface,
    AccessorTraitGeneratorConfigurationInterface
{
    use WithClassGeneratorConfigurationMethods;
    use WithAccessorTraitGeneratorConfigurationMethods;

    /**
     * DataTransportObjectGeneratorConfiguration constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this
            ->parseClassGeneratorConfiguration($configuration)
            ->parseAccessorTraitGeneratorConfiguration($configuration)
        ;
    }
}
