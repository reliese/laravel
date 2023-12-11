<?php

namespace Reliese\Meta;

use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use Reliese\Meta\MySql\Database as MySqlDatabase;
use Reliese\Meta\Postgres\Database as PostgresDatabase;
use Reliese\Meta\Sqlite\Database as SqliteDatabase;
use Reliese\Meta\MySql\Schema as MySqlSchema;
use Reliese\Meta\Postgres\Schema as PostgresSchema;
use Reliese\Meta\Sqlite\Schema as SqliteSchema;

use function get_class;

/**
 * Class AdapterFactory
 */
class AdapterFactory
{
    /**
     * @param \Illuminate\Database\Connection $connection
     * @return DatabaseInterface
     */
    public function database($connection)
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