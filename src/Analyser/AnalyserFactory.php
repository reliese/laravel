<?php

namespace Reliese\Analyser;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use Reliese\Analyser\MySql\MySqlDatabaseAnalyser;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AnalyserFactory
 */
class AnalyserFactory
{
    /**
     * @param ConnectionInterface $connection
     *
     * @return DatabaseAnalyserInterface
     */
    public function database(ConnectionInterface $connection, OutputInterface $output): DatabaseAnalyserInterface
    {
        switch (get_class($connection)) {
            case \Larapack\DoctrineSupport\Connections\MySqlConnection::class:
            case MySqlConnection::class:
                return new MySqlDatabaseAnalyser($connection, $output);
            case SQLiteConnection::class:
                throw new \RuntimeException("Unable to locate a Postgress compatible implementation of " . DatabaseAnalyserInterface::class);
            case PostgresConnection::class:
                throw new \RuntimeException("Unable to locate a SqlLite compatible implementation of " . DatabaseAnalyserInterface::class);
        }

        throw new \RuntimeException(__METHOD__ . " does not define an implementation for " . DatabaseAnalyserInterface::class);
    }
}