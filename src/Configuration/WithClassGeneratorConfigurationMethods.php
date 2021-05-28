<?php

namespace Reliese\Configuration;

use function array_key_exists;
use function call_user_func;
/**
 * Trait ClassGeneratorTrait
 */
trait WithClassGeneratorConfigurationMethods
{
    # region properties
    /**
     * @var string
     */
    private string $classNamespace;

    /**
     * @var string
     */
    private string $classPrefix;

    /**
     * @var string
     */
    private string $classSuffix;

    /**
     * @var string
     */
    private string $generatedClassNamespace;

    /**
     * @var string
     */
    private string $generatedClassPrefix;

    /**
     * @var string
     */
    private string $generatedClassSuffix;
    # endregion properties

    # region accessors

    /**
     * @return string
     */
    public function getClassNamespace(): string
    {
        return $this->classNamespace;
    }

    /**
     * @param string $classNamespace
     *
     * @return static
     */
    public function setClassNamespace(string $classNamespace): static
    {
        $this->classNamespace = $classNamespace;
        return $this;
    }

    /**
     * @return string
     */
    public function getClassPrefix(): string
    {
        return $this->classPrefix;
    }

    /**
     * @param string $classPrefix
     *
     * @return static
     */
    public function setClassPrefix(string $classPrefix): static
    {
        $this->classPrefix = $classPrefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getClassSuffix(): string
    {
        return $this->classSuffix;
    }

    /**
     * @param string $classSuffix
     *
     * @return static
     */
    public function setClassSuffix(string $classSuffix): static
    {
        $this->classSuffix = $classSuffix;
        return $this;
    }

    /**
     * @return string
     */
    public function getGeneratedClassNamespace(): string
    {
        return $this->generatedClassNamespace;
    }

    /**
     * @param string $generatedClassNamespace
     *
     * @return static
     */
    public function setGeneratedClassNamespace(string $generatedClassNamespace): static
    {
        $this->generatedClassNamespace = $generatedClassNamespace;
        return $this;
    }

    /**
     * @return string
     */
    public function getGeneratedClassPrefix(): string
    {
        return $this->generatedClassPrefix;
    }

    /**
     * @param string $generatedClassPrefix
     *
     * @return static
     */
    public function setGeneratedClassPrefix(string $generatedClassPrefix): static
    {
        $this->generatedClassPrefix = $generatedClassPrefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getGeneratedClassSuffix(): string
    {
        return $this->generatedClassSuffix;
    }

    /**
     * @param string $generatedClassSuffix
     *
     * @return static
     */
    public function setGeneratedClassSuffix(string $generatedClassSuffix): static
    {
        $this->generatedClassSuffix = $generatedClassSuffix;
        return $this;
    }
    # endregion accessors

    /**
     * @param array $configurationSection
     */
    protected function parseClassGeneratorConfiguration(array $configurationSection): static
    {
        $requiredConfigurationKeys = [
            'ClassNamespace' => [$this, 'setClassNamespace'],
            'ClassPrefix' => [$this, 'setClassPrefix'],
            'ClassSuffix' => [$this, 'setClassSuffix'],
            'GeneratedClassNamespace' => [$this, 'setGeneratedClassNamespace'],
            'GeneratedClassPrefix' => [$this, 'setGeneratedClassPrefix'],
            'GeneratedClassSuffix' => [$this, 'setGeneratedClassSuffix'],
        ];

        foreach ($requiredConfigurationKeys as $configurationKey => $delegate) {
            if (!array_key_exists($configurationKey, $configurationSection)) {
                throw new \RuntimeException("Reliese Configuration section for ".static::class." must specify a value for \"$configurationKey\"");
            }
            call_user_func($delegate, $configurationSection[$configurationKey]);
        }
        return $this;
    }
}