<?php

namespace Reliese\Configuration;

use Reliese\Filter\SchemaFilter;

/**
 * Class DatabaseAnalyserConfiguration
 */
class DatabaseAnalyserConfiguration
{
    /**
     * @var mixed
     */
    private string $connectionName;

    /**
     * @var RelieseConfiguration
     */
    private RelieseConfiguration $relieseConfiguration;

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

        $this->analyserClass = $configuration['AnalyserClass'];
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
     * @return SchemaFilter
     */
    public function getSchemaFilter(): SchemaFilter
    {
        return $this->relieseConfiguration->getDatabaseBlueprintConfiguration()->getSchemaFilter();
    }
}
