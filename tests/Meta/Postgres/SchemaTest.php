<?php

namespace Reliese\Tests\Meta\Postgres;

use Illuminate\Database\PostgresConnection;
use Mockery;
use Reliese\Meta\Postgres\Schema;
use Reliese\Tests\TestCase;

class SchemaTest extends TestCase
{
    /**
     * @var PostgresConnection
     */
    private $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->connection = Mockery::mock(PostgresConnection::class);

        $this->connection->shouldReceive('raw')->andReturns();
        $this->connection->shouldReceive('select')->andReturns(
            /** $names in @see \Reliese\Meta\Postgres\Schema::fetchTables() */
            [],

            /** $relations in @see \Reliese\Meta\Postgres\Schema::fillConstraints() */
            [],

            /** $indexes in @see \Reliese\Meta\Postgres\Schema::fillConstraints() */
            [],
        );
        $this->connection->shouldReceive('getName')->andReturn('testdatabase');
    }

    public function test_it_can_be_instantiated()
    {
        $schema = new Schema('schema', $this->connection);

        $this->assertSame('schema', $schema->schema());
        $this->assertSame(['public'], $schema->schemanames());
        $this->assertFalse($schema->has('test'));
        $this->assertSame([], $schema->tables());
        $this->assertSame($this->connection, $schema->connection());
    }

    public function test_it_search_path()
    {
        $schemanames = [
            'public',
            'another_schema',
        ];
        $this->app['config']->set('database.connections.test_connection.search_path', $schemanames);
        $this->app['config']->set('database.connections.test_connection.schema', 'schemaname');
        $schema = new Schema('schema', $this->connection, 'test_connection');

        $this->assertSame($schemanames, $schema->schemanames());
    }

    public function test_it_schema()
    {
        $schema = 'schemaname';
        $schemanames = [
            $schema,
        ];
        $this->app['config']->set('database.connections.test_connection.schema', $schema);
        $schema = new Schema('schema', $this->connection, 'test_connection');

        $this->assertSame($schemanames, $schema->schemanames());
    }

    public function provideSchemasPermutations()
    {
        return [
            // connectionName, database, expected
            ['pgsql', 'testdatabase', ['testdatabase']],
            ['another_connection', 'another_database', ['another_database']],
        ];
    }

    /**
     * @dataProvider provideSchemasPermutations
     */
    public function testSchemas($connectionName, $database, $expected)
    {
        $this->app['config']->set(sprintf('database.connections.%s.database', $connectionName), $database);

        $this->assertSame($expected, Schema::schemas($this->connection, $connectionName));
    }
}
