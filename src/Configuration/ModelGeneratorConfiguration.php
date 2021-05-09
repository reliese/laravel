<?php

namespace Reliese\Configuration;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelGeneratorConfiguration
 */
class ModelGeneratorConfiguration
{
    const KEY_PATH = 'Path';
    const KEY_NAMESPACE = 'Namespace';
    const KEY_CLASS_SUFFIX = 'ClassSuffix';
    const KEY_PARENT_CLASS_PREFIX = 'ParentClassPrefix';
    const KEY_PARENT = 'Parent';
    const KEY_TRAITS = 'Traits';
    const KEY_APPEND_CONNECTION = 'AppendConnection';

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
     * @var string
     */
    private string $parent;

    /**
     * @var string[]
     */
    private array $traits = [];

    /**
     * @var bool
     */
    private bool $appendConnection;

    /**
     * ModelGeneratorConfiguration constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration = [])
    {
        $this->setPath($configuration[static::KEY_PATH] ?? '');
        $this->setNamespace($configuration[static::KEY_NAMESPACE] ?? '');
        $this->setClassSuffix($configuration[static::KEY_CLASS_SUFFIX] ?? '');
        $this->setParentClassPrefix($configuration[static::KEY_PARENT_CLASS_PREFIX] ?? '');
        $this->setParent($configuration[static::KEY_PARENT] ?? Model::class);
        $this->setTraits($configuration[static::KEY_TRAITS] ?? []);
        $this->appendConnection($configuration[static::KEY_APPEND_CONNECTION] ?? false);
    }

    /**
     * @return string
     */
    public function getClassSuffix(): string
    {
        return $this->classSuffix;
    }

    /**
     * @return bool
     */
    public function hasClassSuffix(): bool
    {
        return !empty($this->getClassSuffix());
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getParentClassPrefix(): string
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
     * @param string $path
     *
     * @return ModelGeneratorConfiguration
     */
    public function setPath(string $path): ModelGeneratorConfiguration
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return $this->parent;
    }

    /**
     * @param string $parent
     *
     * @return ModelGeneratorConfiguration
     */
    public function setParent(string $parent): ModelGeneratorConfiguration
    {
        $this->parent = trim($parent, '\\');
        return $this;
    }

    /**
     * @param string $classSuffix
     *
     * @return ModelGeneratorConfiguration
     */
    public function setClassSuffix(mixed $classSuffix): ModelGeneratorConfiguration
    {
        $this->classSuffix = $classSuffix;
        return $this;
    }

    /**
     * @param string $namespace
     *
     * @return ModelGeneratorConfiguration
     */
    public function setNamespace(string $namespace): ModelGeneratorConfiguration
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @param string $parentClassPrefix
     *
     * @return ModelGeneratorConfiguration
     */
    public function setParentClassPrefix(string $parentClassPrefix): ModelGeneratorConfiguration
    {
        $this->parentClassPrefix = $parentClassPrefix;
        return $this;
    }

    /**
     * @param string[] $traits
     */
    public function setTraits(array $traits)
    {
        $this->traits = $traits;
    }

    /**
     * @return string[]
     */
    public function getTraits(): array
    {
        return $this->traits;
    }

    /**
     * @param bool $append
     *
     * @return $this
     */
    public function appendConnection(bool $append): static
    {
        $this->appendConnection = $append;

        return $this;
    }

    /**
     * @return bool
     */
    public function appendsConnection(): bool
    {
        return $this->appendConnection;
    }
}
