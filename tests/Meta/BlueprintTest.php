<?php

use Pursehouse\Modeler\Meta\Blueprint;

class BlueprintTest extends TestCase
{
    public function test_it_can_be_instantiated()
    {
        $blueprint = new Blueprint('connection', 'schema', 'table');

        $this->assertEquals('connection', $blueprint->connection());
        $this->assertEquals('schema', $blueprint->schema());
        $this->assertEquals('table', $blueprint->table());
    }
}
