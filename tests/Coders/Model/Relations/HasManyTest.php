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
            [false, 'Person', 'PublishedBook', 'id', 'authorId', 'publishedBooksWhereAuthor'],
            [false, 'Person', 'PublishedBook', 'ID', 'authorID', 'publishedBooksWhereAuthor'],
            [false, 'Person', 'PublishedBook', 'id', 'personId', 'publishedBooks'],
            [false, 'Person', 'PublishedBook', 'ID', 'personID', 'publishedBooks'],
            // snake_case
            [true, 'Person', 'PublishedBook', 'id', 'author_id', 'published_books_where_author'],
            [true, 'Person', 'PublishedBook', 'id', 'person_id', 'published_books'],
            [true, 'Person', 'PublishedBook', 'ID', 'author_id', 'published_books_where_author'],
            [true, 'Person', 'PublishedBook', 'ID', 'person_id', 'published_books'],
            // no suffix
            [false, 'Person', 'PublishedBook', 'id', 'author', 'publishedBooksWhereAuthor'],
            [false, 'Person', 'PublishedBook', 'id', 'person', 'publishedBooks'],
            [true, 'Person', 'PublishedBook', 'id', 'author', 'published_books_where_author'],
            [true, 'Person', 'PublishedBook', 'id', 'person', 'published_books'],
            // same table reference
            [false, 'Person', 'Person', 'id', 'lineManagerId', 'peopleWhereLineManager'],
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

    public function provideRelatedStrategyPermutations()
    {
        // usesSnakeAttributes, subjectName, relatedName, expected
        return [
            [false, 'Person', 'PublishedBook', 'publishedBooks'],
            [true, 'Person', 'PublishedBook', 'published_books'],
            // Same table reference
            [false, 'Person', 'Person', 'people']
        ];
    }

    /**
     * @dataProvider provideRelatedStrategyPermutations
     *
     * @param bool $usesSnakeAttributes
     * @param string $subjectName
     * @param string $relationName
     * @param string $expected
     */
    public function testNameUsingRelatedStrategy($usesSnakeAttributes, $subjectName, $relationName, $expected)
    {
        $relation = Mockery::mock(Fluent::class)->makePartial();

        $relatedModel = Mockery::mock(Model::class)->makePartial();
        $relatedModel->shouldReceive('getClassName')->andReturn($relationName);

        $subject = Mockery::mock(Model::class)->makePartial();
        $subject->shouldReceive('getClassName')->andReturn($subjectName);
        $subject->shouldReceive('getRelationNameStrategy')->andReturn('related');
        $subject->shouldReceive('usesSnakeAttributes')->andReturn($usesSnakeAttributes);

        /** @var BelongsTo|\Mockery\Mock $relationship */
        $relationship = Mockery::mock(HasMany::class, [$relation, $subject, $relatedModel])->makePartial();

        $this->assertEquals(
            $expected,
            $relationship->name(),
            json_encode(compact('usesSnakeAttributes', 'subjectName', 'relationName'))
        );
    }
}
