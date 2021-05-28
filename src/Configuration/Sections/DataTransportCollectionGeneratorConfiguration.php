<?php

namespace Reliese\Configuration\Sections;;

use Reliese\Configuration\ClassGeneratorConfigurationInterface;
use Reliese\Configuration\WithClassGeneratorConfigurationMethods;

/**
 * Class DataTransportCollectionGeneratorConfiguration
 */
class DataTransportCollectionGeneratorConfiguration implements ClassGeneratorConfigurationInterface
{
    use WithClassGeneratorConfigurationMethods;

    /**
     * DataTransportObjectGeneratorConfiguration constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->parseClassGeneratorConfiguration($configuration);
    }
}
