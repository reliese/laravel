<?php

namespace Reliese\Analyser\MySql;

use Illuminate\Database\MySqlConnection;
use Reliese\Analyser\DatabaseAnalyserInterface;
use Reliese\Analyser\SchemaAnalyserInterface;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\DatabaseDescriptionDto;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MySqlDatabaseAnalyser
 */
class MySqlDatabaseAnalyser implements DatabaseAnalyserInterface
{
    const COMPATIBILITY_TYPE_NAME = 'MySql';

    /**
     * @var MySqlConnection
     */
    private $connection;

    /**
     * @var MySqlSchemaAnalyser[]
     */
    private $mySqlSchemaAnalysers = [];

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * MySqlDatabaseAnalyser constructor.
     *
     * @param MySqlConnection $connection
     * @param OutputInterface     $output
     */
    public function __construct(MySqlConnection $connection, OutputInterface $output)
    {
        $this->connection = $connection;
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function getCompatibilityTypeName(): string
    {
        return static::COMPATIBILITY_TYPE_NAME;
    }

    /**
     * @return MySqlConnection
     */
    public function getConnection(): MySqlConnection
    {
        return $this->connection;
    }

    /**
     * @return DatabaseBlueprint
     */
    public function analyseDatabase(): DatabaseBlueprint
    {
        $databaseDescriptionDto = new DatabaseDescriptionDto($this->getCompatibilityTypeName(),
            $this->getDatabaseName(),
            $this->getReleaseVersion());

        $databaseBlueprint = new DatabaseBlueprint($databaseDescriptionDto);
        /*
         * Create a SchemaBlueprint for each schema
         */
        foreach ($this->getSchemaNames() as $schemaName) {

            $schemaAnalyser = new MySqlSchemaAnalyser($this, $schemaName);

            $schemaBlueprint = $schemaAnalyser->analyseSchema($databaseBlueprint);

            $databaseBlueprint->addSchemaBlueprint($schemaBlueprint);
        }

        return $databaseBlueprint;
    }

    /**
     * @inheritDoc
     */
    protected function getDatabaseName(): string
    {
        $rows = $this->connection->select("SHOW VARIABLES LIKE 'version_comment'");
        return $rows[0]->Value;
    }

    /**
     * @inheritDoc
     */
    protected function getReleaseVersion(): string
    {
        $rows = $this->connection->select("SHOW VARIABLES LIKE 'version'");
        return $rows[0]->Value;
    }

    /**
     * @inheritDoc
     */
    protected function getSchemaNames(): array
    {
        /** @noinspection SqlNoDataSourceInspection */
        $sql = 'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA';

        $rows = $this->getConnection()->select($sql);

        $results = [];
        foreach ($rows as $row) {
            $results[] = $row->SCHEMA_NAME;
        }

        return $results;
    }
}