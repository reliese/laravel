<?php

namespace Reliese\Configuration;

use function array_key_exists;
use function call_user_func;
/**
 * Trait WithAccessorTraitGeneratorConfiguration
 */
trait WithAccessorTraitGeneratorConfigurationMethods
{
    /**
     * @var string
     */
    private string $accessorTraitPrefix;

    /**
     * @var string
     */
    private string $accessorTraitSuffix;

    /**
     * @return string
     */
    public function getAccessorTraitNamespace(): string
    {
        return $this->accessorTraitNamespace;
    }

    /**
     * @param string $accessorTraitNamespace
     *
     * @return static
     */
    public function setAccessorTraitNamespace(string $accessorTraitNamespace): static
    {
        $this->accessorTraitNamespace = $accessorTraitNamespace;
        return $this;
    }
    /**
     * @var string
     */
    private string $accessorTraitNamespace;

    protected function parseAccessorTraitGeneratorConfiguration(array $configurationSection): static
    {
        $requiredConfigurationKeys = [
            'AccessorTraitNamespace' => [$this, 'setAccessorTraitNamespace'],
            'AccessorTraitPrefix' => [$this, 'setAccessorTraitPrefix'],
            'AccessorTraitSuffix' => [$this, 'setAccessorTraitSuffix'],
        ];

        foreach ($requiredConfigurationKeys as $configurationKey => $delegate) {
            if (!array_key_exists($configurationKey, $configurationSection)) {
                throw new \RuntimeException("Reliese Configuration section for ".static::class." must specify a value for \"$configurationKey\"");
            }
            call_user_func($delegate, $configurationSection[$configurationKey]);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getAccessorTraitPrefix(): string
    {
        return $this->accessorTraitPrefix;
    }

    /**
     * @param string $accessorTraitPrefix
     *
     * @return $this
     */
    public function setAccessorTraitPrefix(string $accessorTraitPrefix): static
    {
        $this->accessorTraitPrefix = $accessorTraitPrefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccessorTraitSuffix(): string
    {
        return $this->accessorTraitSuffix;
    }

    /**
     * @param string $accessorTraitSuffix
     *
     * @return $this
     */
    public function setAccessorTraitSuffix(string $accessorTraitSuffix): static
    {
        $this->accessorTraitSuffix = $accessorTraitSuffix;
        return $this;
    }
}