<?php

use Illuminate\Support\Fluent;
use PHPUnit\Framework\TestCase;
use Reliese\Coders\Model\Model;
use Reliese\Coders\Model\Relations\BelongsTo;

class BelongsToTest extends TestCase
{
    public function provideForeignKeyStrategyPermutations()
    {
        // usesSnakeAttributes, primaryKey, foreignKey, expected
        return [
            // columns use camelCase
            [false, 'id', 'lineManagerId', 'lineManager'],
            [false, 'Id', 'lineManagerId', 'lineManager'],
            [false, 'ID', 'lineManagerID', 'lineManager'],
            // columns use snake_case
            [true, 'id', 'line_manager_id', 'line_manager'],
            [true, 'ID', 'line_manager_id', 'line_manager'],
            // foreign keys without primary key suffix
            [false, 'id', 'lineManager', 'lineManager'],
            [true, 'id', 'line_manager', 'line_manager'],
        ];
    }

    /**
     * @dataProvider provideForeignKeyStrategyPermutations
     *
     * @param bool $usesSnakeAttributes
     * @param string $primaryKey
     * @param string $foreignKey
     * @param string $expected
     */
    public function testNameUsingForeignKeyStrategy($usesSnakeAttributes, $primaryKey, $foreignKey, $expected)
    {
        $relation = Mockery::mock(Fluent::class)->makePartial();

        $relatedModel = Mockery::mock(Model::class)->makePartial();

        $subject = Mockery::mock(Model::class)->makePartial();
        $subject->shouldReceive('getRelationNameStrategy')->andReturn('foreign_key');
        $subject->shouldReceive('usesSnakeAttributes')->andReturn($usesSnakeAttributes);

        /** @var BelongsTo|\Mockery\Mock $relationship */
        $relationship = Mockery::mock(BelongsTo::class, [$relation, $subject, $relatedModel])->makePartial();
        $relationship->shouldAllowMockingProtectedMethods();
        $relationship->shouldReceive('otherKey')->andReturn($primaryKey);
        $relationship->shouldReceive('foreignKey')->andReturn($foreignKey);

        $this->assertEquals(
            $expected,
            $relationship->name(),
            json_encode(compact('usesSnakeAttributes', 'primaryKey', 'foreignKey'))
        );
    }

    public function provideRelatedStrategyPermutations()
    {
        // usesSnakeAttributes, relatedClassName, expected
        return [
            [false, 'LineManager', 'lineManager'],
            [true, 'LineManager', 'line_manager'],
        ];
    }

    /**
     * @dataProvider provideRelatedStrategyPermutations
     *
     * @param bool $usesSnakeAttributes
     * @param string $relatedClassName
     * @param string $expected
     */
    public function testNameUsingRelatedStrategy($usesSnakeAttributes, $relatedClassName, $expected)
    {
        $relation = Mockery::mock(Fluent::class)->makePartial();

        $relatedModel = Mockery::mock(Model::class)->makePartial();
        $relatedModel->shouldReceive('getClassName')->andReturn($relatedClassName);

        $subject = Mockery::mock(Model::class)->makePartial();
        $subject->shouldReceive('getRelationNameStrategy')->andReturn('related');
        $subject->shouldReceive('usesSnakeAttributes')->andReturn($usesSnakeAttributes);

        /** @var BelongsTo|\Mockery\Mock $relationship */
        $relationship = Mockery::mock(BelongsTo::class, [$relation, $subject, $relatedModel])->makePartial();

        $this->assertEquals(
            $expected,
            $relationship->name(),
            json_encode(compact('usesSnakeAttributes', 'relatedClassName'))
        );
    }
}
