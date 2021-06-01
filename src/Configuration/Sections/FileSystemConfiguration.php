<?php

namespace Reliese\Configuration\Sections;

/**
 * Class FileSystemConfiguration
 */
class FileSystemConfiguration
{
    const APPLICATION_PATH = 'ApplicationPath';
    const BASE_PATH = 'BasePath';

    protected string $applicationPath;
    protected string $basePath;

    /**
     * FileSystemConfiguration constructor.
     *
     * @param string $applicationPath
     */
    public function __construct(array $configuration)
    {
        $this->applicationPath = $configuration[self::APPLICATION_PATH];
        $this->basePath = $configuration[self::BASE_PATH];
    }

    /**
     * @return string
     */
    public function getApplicationPath(): string
    {
        return $this->applicationPath;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }
}