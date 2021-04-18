<?php

namespace Reliese\Command;

/**
 * Trait ConfigurationProfileOptionTrait
 */
trait ConfigurationProfileOptionTrait
{

    protected static $configurationProfileOptionDescription = '
                            {--p|profile=default : The name of the configuration profile from config/reliease.php}';

    /**
     * @return string
     */
    protected function getConfigurationProfileName()
    {
        return $this->option('profile') ?? 'default';
    }
}
