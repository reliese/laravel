<?php

namespace Reliese\Analyser\MySql;

use Illuminate\Database\ConnectionInterface;

/**
 * Trait WithMySqlSchemaAnalyserTrait
 */
trait MySqlSchemaAnalyserTrait
{
    /**
     * @var MySqlSchemaAnalyser
     */
    protected $mySqlSchemaAnalyser;

    /**
     * @return MySqlSchemaAnalyser
     */
    public function getMySqlSchemaAnalyser() : MySqlSchemaAnalyser
    {
        return $this->mySqlSchemaAnalyser;
    }

    /**
     * @param MySqlSchemaAnalyser $mySqlSchemaAnalyser
     */
    public function setMySqlSchemaAnalyser(MySqlSchemaAnalyser $mySqlSchemaAnalyser)
    {
        $this->mySqlSchemaAnalyser = $mySqlSchemaAnalyser;
    }

    /**
     * @return string
     */
    public function getSchemaName() : string
    {
        return $this->getMySqlSchemaAnalyser()->getSchemaName();
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection() : ConnectionInterface
    {
        return $this->getMySqlSchemaAnalyser()->getConnection();
    }
}