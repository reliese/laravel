<?php

use Illuminate\Support\Fluent;
use PHPUnit\Framework\TestCase;
use Reliese\Coders\Model\Model;
use Reliese\Coders\Model\Relations\BelongsTo;
use Reliese\Coders\Model\Relations\HasMany;

class HasManyTest extends TestCase
{
    public function provideForeignKeyStrategyPermutations()
    {
        // usesSnakeAttributes, subjectName, relationName, primaryKey, foreignKey, expected
        return [
            // camelCase
            [false, 'Person', 'Book', 'id', 'authorId', 'booksWhereAuthor'],
            [false, 'Person', 'Book', 'ID', 'authorID', 'booksWhereAuthor'],
            [false, 'Person', 'Book', 'id', 'personId', 'books'],
            [false, 'Person', 'Book', 'ID', 'personID', 'books'],
            // snake_case
            [true, 'Person', 'Book', 'id', 'author_id', 'books_where_author'],
            [true, 'Person', 'Book', 'id', 'person_id', 'books'],
            [true, 'Person', 'Book', 'ID', 'author_id', 'books_where_author'],
            [true, 'Person', 'Book', 'ID', 'person_id', 'books'],
            // no suffix
            [false, 'Person', 'Book', 'id', 'author', 'booksWhereAuthor'],
            [false, 'Person', 'Book', 'id', 'person', 'books'],
            [true, 'Person', 'Book', 'id', 'author', 'books_where_author'],
            [true, 'Person', 'Book', 'id', 'person', 'books'],
        ];
    }

    /**
     * @dataProvider provideForeignKeyStrategyPermutations
     *
     * @param bool $usesSnakeAttributes
     * @param string $subjectName
     * @param string $relationName
     * @param string $primaryKey
     * @param string $foreignKey
     * @param string $expected
     */
    public function testNameUsingForeignKeyStrategy($usesSnakeAttributes, $subjectName, $relationName, $primaryKey, $foreignKey, $expected)
    {
        $relation = Mockery::mock(Fluent::class)->makePartial();

        $relatedModel = Mockery::mock(Model::class)->makePartial();
        $relatedModel->shouldReceive('getClassName')->andReturn($relationName);

        $subject = Mockery::mock(Model::class)->makePartial();
        $subject->shouldReceive('getRelationNameStrategy')->andReturn('foreign_key');
        $subject->shouldReceive('usesSnakeAttributes')->andReturn($usesSnakeAttributes);
        $subject->shouldReceive('getClassName')->andReturn($subjectName);

        /** @var BelongsTo|\Mockery\Mock $relationship */
        $relationship = Mockery::mock(HasMany::class, [$relation, $subject, $relatedModel])->makePartial();
        $relationship->shouldAllowMockingProtectedMethods();
        $relationship->shouldReceive('localKey')->andReturn($primaryKey);
        $relationship->shouldReceive('foreignKey')->andReturn($foreignKey);

        $this->assertEquals(
            $expected,
            $relationship->name(),
            json_encode(compact('usesSnakeAttributes', 'subjectName', 'relationName', 'primaryKey', 'foreignKey'))
        );
    }
}
