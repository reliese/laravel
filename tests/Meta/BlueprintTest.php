<?php

namespace Reliese\Tests\Meta;

use Reliese\Meta\Blueprint;
use Reliese\Tests\TestCase;

/**
 * Created by Cristian.
 * Date: 16/10/16 01:32 PM.
 */
class BlueprintTest extends TestCase
{
    public function test_it_can_be_instantiated()
    {
        $blueprint = new Blueprint('connection', 'schema', 'table');

        $this->assertSame('connection', $blueprint->connection());
        $this->assertSame('schema', $blueprint->schema());
        $this->assertSame('table', $blueprint->table());
        $this->assertFalse($blueprint->isView());
        $this->assertSame('', $blueprint->schemaname());
    }

    public function test_it_is_view()
    {
        $blueprint = new Blueprint('connection', 'schema', 'table', true);

        $this->assertSame('connection', $blueprint->connection());
        $this->assertSame('schema', $blueprint->schema());
        $this->assertSame('table', $blueprint->table());
        $this->assertTrue($blueprint->isView());
        $this->assertSame('', $blueprint->schemaname());
    }

    public function test_it_schemaname()
    {
        $blueprint = new Blueprint('connection', 'schema', 'table', false, 'schemaname');

        $this->assertSame('connection', $blueprint->connection());
        $this->assertSame('schema', $blueprint->schema());
        $this->assertSame('table', $blueprint->table());
        $this->assertFalse($blueprint->isView());
        $this->assertSame('schemaname', $blueprint->schemaname());
    }


    public function provideQualifiedTablePermutations()
    {
        return [
            // schema, table, schemaname, expected
            ['schema', 'table', '', 'schema.table'],
            ['schema', 'table', 'schemaname', 'schemaname.table'],
        ];
    }

    /**
     * @dataProvider provideQualifiedTablePermutations
     */
    public function testQualifiedTable($schema, $table, $schemaname, $expected)
    {
        $blueprint = new Blueprint('connection', $schema, $table, false, $schemaname);

        $this->assertSame($expected, $blueprint->qualifiedTable());
    }
}
