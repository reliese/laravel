<?php

namespace Reliese\Configuration\Sections;;

use Reliese\Filter\DatabaseFilter;

/**
 * Class DatabaseAnalyserConfiguration
 */
class DatabaseAnalyserConfiguration
{
    const DOCTRINE_DATABASE_ASSISTANT_CLASS = 'DoctrineDatabaseAssistantClass';
    const CONNECTION_NAME = 'ConnectionName';

    /**
     * @var mixed
     */
    private string $connectionName;

    /**
     * @var string
     */
    private string $doctrineDatabaseAssistantClass;

    /**
     * DatabaseAnalyserConfiguration constructor.
     *
     * @param array $configuration
     */
    public function __construct(
        array $configuration
    ) {
        if (empty($configuration['ConnectionName'])) {
            throw new \InvalidArgumentException("DatabaseAnalyserConfiguration must define a key-value pair for \"ConnectionName\"");
        }

        $this->doctrineDatabaseAssistantClass = $configuration['DoctrineDatabaseAssistantClass'];
        $this->connectionName = $configuration['ConnectionName'];
    }

    /**
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    /**
     * Database Connection Name
     * --------------------------------------------------------------------------
     * The name of the database connection from the Laravel database.php file
     * that should be used to populate schema and table blueprints
     *
     * @param mixed $connectionName
     *
     * @return DatabaseAnalyserConfiguration
     */
    public function setConnectionName(mixed $connectionName): DatabaseAnalyserConfiguration
    {
        $this->connectionName = $connectionName;
        return $this;
    }

    /**
     * @return string
     */
    public function getDoctrineDatabaseAssistantClass(): string
    {
        return $this->doctrineDatabaseAssistantClass;
    }

    /**
     * Doctrine Database Assistant Class
     * --------------------------------------------------------------------------
     * The class that will be used to handle database platform specific analysis.
     *
     * As an example, SqlLite doesn't support multiple schemas, but MySql does.
     *
     * @param string $doctrineDatabaseAssistantClass
     *
     * @return DatabaseAnalyserConfiguration
     */
    public function setDoctrineDatabaseAssistantClass(string $doctrineDatabaseAssistantClass): DatabaseAnalyserConfiguration
    {
        $this->doctrineDatabaseAssistantClass = $doctrineDatabaseAssistantClass;
        return $this;
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return [
            static::DOCTRINE_DATABASE_ASSISTANT_CLASS => $this->getDoctrineDatabaseAssistantClass(),
            static::CONNECTION_NAME => $this->getConnectionName(),
        ];
    }
}
