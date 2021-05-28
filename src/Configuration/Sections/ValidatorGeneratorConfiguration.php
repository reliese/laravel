<?php

namespace Reliese\Configuration\Sections;;

use Reliese\Configuration\AccessorTraitGeneratorConfigurationInterface;
use Reliese\Configuration\ClassGeneratorConfigurationInterface;
use Reliese\Configuration\WithAccessorTraitGeneratorConfigurationMethods;
use Reliese\Configuration\WithClassGeneratorConfigurationMethods;
use Reliese\Configuration\WithDatabaseFilters;
use Reliese\Filter\DatabaseFilter;
/**
 * Class ValidatorGeneratorConfiguration
 */
class ValidatorGeneratorConfiguration implements ClassGeneratorConfigurationInterface,
    AccessorTraitGeneratorConfigurationInterface
{
    use WithDatabaseFilters;
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
            ->parseDatabaseFilters($configuration)
        ;
    }
}