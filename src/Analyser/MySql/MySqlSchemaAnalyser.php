<?php

namespace Reliese\Analyser\MySql;

use Illuminate\Database\MySqlConnection;
use Reliese\Blueprint\DatabaseBlueprint;
use Reliese\Blueprint\SchemaBlueprint;

/**
 * Class MySqlSchemaAnalyser
 */
class MySqlSchemaAnalyser
{
    /**
     * @var MySqlDatabaseAnalyser
     */
    private $mySqlDatabaseAnalyser;

    /**
     * @var string
     */
    private $schemaName;

    /**
     * MySqlSchemaAnalyser constructor.
     *
     * @param MySqlDatabaseAnalyser $mySqlDatabaseAnalyser
     * @param string                $schemaName
     */
    public function __construct(MySqlDatabaseAnalyser $mySqlDatabaseAnalyser, string $schemaName)
    {
        $this->schemaName = $schemaName;
        $this->mySqlDatabaseAnalyser = $mySqlDatabaseAnalyser;
    }

    /**
     * @param DatabaseBlueprint $databaseBlueprint
     *
     * @return SchemaBlueprint
     */
    public function analyseSchema(DatabaseBlueprint $databaseBlueprint): SchemaBlueprint
    {
        $schemaBlueprint = new SchemaBlueprint($databaseBlueprint, $this->schemaName);

        if (!empty($this->getTableNames())) {
            $tableAnalyser = new MySqlTableAnalyser($this);
            foreach ($this->getTableNames() as $tableName) {
                $tableAnalyser->analyseTable($schemaBlueprint, $tableName);
            }
        }

        if (!empty($this->getTableNames())) {
            $viewAnalyser = new MySqlViewAnalyser($this);
            foreach ($this->getViewNames() as $viewName) {
                $viewAnalyser->analyseView($schemaBlueprint, $viewName);
            }
        }

        return $schemaBlueprint;
    }

    /**
     * @return string
     */
    public function getSchemaName(): string
    {
        return $this->schemaName;
    }

    /**
     * @inheritDoc
     */
    protected function getTableNames(): array
    {
        /** @noinspection SqlNoDataSourceInspection */
        $sql = "
SELECT
    TABLE_NAME
FROM
     information_schema.TABLES as t
WHERE
    t.TABLE_SCHEMA = '{$this->schemaName}'
AND t.TABLE_TYPE = 'BASE TABLE'
";

        $tableNames = [];

        foreach ($this->getConnection()->select($sql) as $row) {
            $tableName[] = $row->TABLE_NAME;
        }

        return $tableNames;
    }

    /**
     * @inheritDoc
     */
    protected function getViewNames(): array
    {
        /** @noinspection SqlNoDataSourceInspection */
        $sql = "
SELECT
    TABLE_NAME
FROM
     information_schema.TABLES as t
WHERE
    t.TABLE_SCHEMA = '{$this->schemaName}'
AND t.TABLE_TYPE = 'VIEW'
";

        $viewNames = [];

        foreach ($this->getConnection()->select($sql) as $row) {
            $viewName[] = $row->TABLE_NAME;
        }

        return $viewNames;
    }

    /**
     * @return MySqlConnection
     */
    public function getConnection(): MySqlConnection
    {
        return $this->mySqlDatabaseAnalyser->getConnection();
    }
}