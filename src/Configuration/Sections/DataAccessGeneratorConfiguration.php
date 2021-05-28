<?php

namespace Reliese\Configuration\Sections;;

use Reliese\Configuration\ClassGeneratorConfigurationInterface;
use Reliese\Configuration\WithClassGeneratorConfigurationMethods;

/**
 * Class DataAccessGeneratorConfiguration
 */
class DataAccessGeneratorConfiguration implements ClassGeneratorConfigurationInterface
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
