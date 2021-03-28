<?php

namespace Reliese\Blueprint;

/**
 * Class DatabaseDescriptionDto
 */
class DatabaseDescriptionDto
{
    /**
     * @var string
     */
    private $compatibilityTypeName;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var string
     */
    private $releaseVersion;

    /**
     * ReleaseVersionDto constructor.
     *
     * @param string $compatibilityTypeName example: MySql, Postgress, SqlLite, Microsoft SqlServer
     * @param string $databaseName          example: MySql versus MariaDb, or MsSqlServer Developer vs MsSqlServer
     *                                      Developer vs MsSqlServer Enterprise
     * @param string $releaseVersion        example: 10.5.9-MariaDB
     */
    public function __construct(
        string $compatibilityTypeName,
        string $databaseName,
        string $releaseVersion
    ) {
        $this->compatibilityTypeName = $compatibilityTypeName;
        $this->databaseName = $databaseName;
        $this->releaseVersion = $releaseVersion;
    }

    /**
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    /**
     * @return string
     */
    public function getReleaseVersion(): string
    {
        return $this->releaseVersion;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return $this->compatibilityTypeName;
    }
}