<?php

namespace Reliese\Meta;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use Reliese\Meta\MySql\Database as MySqlDatabase;
use Reliese\Meta\Postgres\Database as PostgresDatabase;
use Reliese\Meta\Sqlite\Database as SqliteDatabase;

use function get_class;

/**
 * Class AdapterFactory
 */
class AdapterFactory
{
    /**
     * @param ConnectionInterface $connection
     * @return DatabaseInterface
     */
    public function database(ConnectionInterface $connection): DatabaseInterface
    {
        switch (get_class($connection)) {
            case \Larapack\DoctrineSupport\Connections\MySqlConnection::class:
            case MySqlConnection::class:
                /** @noinspection PhpParamsInspection */
                return new MySqlDatabase($connection);
            case SQLiteConnection::class:
                /** @noinspection PhpParamsInspection */
                return new SqliteDatabase($connection);
            case PostgresConnection::class:
                /** @noinspection PhpParamsInspection */
                return new PostgresDatabase($connection);
        }

        throw new \RuntimeException(__METHOD__." does not define an implementation for ".DatabaseInterface::class);
    }
}