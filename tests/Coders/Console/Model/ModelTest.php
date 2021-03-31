<?php

use Illuminate\Support\Fluent;
use Reliese\Coders\Model\Factory;
use Reliese\Coders\Model\Model;
use Reliese\Coders\Model\Relations\BelongsTo;
use Reliese\Meta\Blueprint;

class ModelTest extends TestCase
{
    public function dataForTestPhpTypeHint()
    {
        return [
            'Non-nullable int' => [
                'castType' => 'int',
                'nullable' => false,
                'expect' => 'int',
            ],
            'Nullable int' => [
                'castType' => 'int',
                'nullable' => true,
                'expect' => 'int|null',
            ],
            'Non-nullable json' => [
                'castType' => 'json',
                'nullable' => false,
                'expect' => 'array',
            ],
            'Nullable json' => [
                'castType' => 'json',
                'nullable' => true,
                'expect' => 'array|null',
            ],
            'Non-nullable date' => [
                'castType' => 'date',
                'nullable' => false,
                'expect' => '\Carbon\Carbon',
            ],
            'Nullable date' => [
                'castType' => 'date',
                'nullable' => true,
                'expect' => '\Carbon\Carbon|null',
            ],
        ];
    }

    /**
     * @dataProvider dataForTestPhpTypeHint
     *
     * @param string $castType
     * @param bool $nullable
     * @param string $expect
     */
    public function testPhpTypeHint($castType, $nullable, $expect)
    {
        $model = new Model(
            new Blueprint('test', 'test', 'test'),
            new Factory(
                \Mockery::mock(\Illuminate\Database\DatabaseManager::class),
                \Mockery::mock(Illuminate\Filesystem\Filesystem::class),
                \Mockery::mock(\Reliese\Support\Classify::class),
                new \Reliese\Coders\Model\Config($this->getSampleConfig())
            )
        );

        $result = $model->phpTypeHint($castType, $nullable);
        $this->assertSame($expect, $result);
    }

    /**
     * @dataProvider provideDataForTestNullableRelationships
     * @param bool $nullable
     * @param string $expectedTypehint
     */
    public function testBelongsToNullableRelationships($nullable, $expectedTypehint)
    {
        $columnBag = new \Reliese\Meta\ColumnBag();
        $columnBag->withName('')
            ->withTypeString();

        if ($nullable) {
            $columnBag->isNullable();
        }

        $columnDefinition = $columnBag->asColumn();

        $primaryKey = new \Reliese\Meta\Index(\Reliese\Meta\Index::NAME_PRIMARY, '', []);

        $baseBlueprint = Mockery::mock(Blueprint::class);
        $baseBlueprint->shouldReceive('columns')->andReturn([$columnDefinition]);
        $baseBlueprint->shouldReceive('schema')->andReturn('test');
        $baseBlueprint->shouldReceive('qualifiedTable')->andReturn('test.test');
        $baseBlueprint->shouldReceive('connection')->andReturn('test');
        $baseBlueprint->shouldReceive('primaryKey')->andReturn($primaryKey);
        $baseBlueprint->shouldReceive('relations')->andReturn([]);
        $baseBlueprint->shouldReceive('table')->andReturn('things');
        $baseBlueprint->shouldReceive('column')->andReturn($columnDefinition);

        $model = new Model(
            $baseBlueprint,
            new Factory(
                \Mockery::mock(\Illuminate\Database\DatabaseManager::class),
                \Mockery::mock(Illuminate\Filesystem\Filesystem::class),
                \Mockery::mock(\Reliese\Support\Classify::class),
                new \Reliese\Coders\Model\Config($this->getSampleConfig())
            )
        );

        $relationRule = new \Reliese\Meta\Relation( '', [$columnDefinition->getName()], [], []);

        $relation = new BelongsTo($relationRule,
            $model,
            $model
        );

        $this->assertSame($expectedTypehint, $relation->hint());
    }

    public function provideDataForTestNullableRelationships()
    {
        return [
            'Nullable Relation' => [
                true, '\\App\\Models\\Thing|null'
            ],
            'Non Nullable Relation' => [
                false, '\\App\\Models\\Thing'
            ]
        ];
    }

    /**
     * @return array[]
     */
    private function getSampleConfig(): array
    {
        return [
            '*' => [
                'path' => 'Models',
                'namespace' => 'App\Models',
                'parent' => Illuminate\Database\Eloquent\Model::class,
                'use' => [],
                'connection' => false,
                'timestamps' => true,
                'soft_deletes' => true,
                'date_format' => 'Y-m-d H:i:s',
                'per_page' => 15,
                'base_files' => false,
                'snake_attributes' => true,
                'indent_with_space' => 0,
                'qualified_tables' => false,
                'hidden' => [
                    '*secret*',
                    '*password',
                    '*token',
                ],
                'guarded' => [// 'created_by', 'updated_by'
                ],
                'casts' => [
                    '*_json' => 'json',
                ],
                'except' => [
                    'migrations',
                ],
                'only' => [// 'users',
                ],
                'table_prefix' => '',
                'lower_table_name_first' => false,
                'model_names' => [

                ],
                'relation_name_strategy' => 'related',
                'with_property_constants' => false,
                'pluralize' => true,
                'override_pluralize_for' => [],
            ]
        ];
    }
}
