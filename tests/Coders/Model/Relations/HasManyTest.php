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
            [false, 'StaffMember', 'BlogPost', 'id', 'authorId', 'blogPostsWhereAuthor'],
            [false, 'StaffMember', 'BlogPost', 'ID', 'authorID', 'blogPostsWhereAuthor'],
            [false, 'StaffMember', 'BlogPost', 'id', 'staffMemberId', 'blogPosts'],
            [false, 'StaffMember', 'BlogPost', 'ID', 'staffMemberID', 'blogPosts'],
            // snake_case
            [true, 'StaffMember', 'BlogPost', 'id', 'author_id', 'blog_posts_where_author'],
            [true, 'StaffMember', 'BlogPost', 'id', 'staff_member_id', 'blog_posts'],
            [true, 'StaffMember', 'BlogPost', 'ID', 'author_id', 'blog_posts_where_author'],
            [true, 'StaffMember', 'BlogPost', 'ID', 'staff_member_id', 'blog_posts'],
            // no suffix
            [false, 'StaffMember', 'BlogPost', 'id', 'author', 'blogPostsWhereAuthor'],
            [false, 'StaffMember', 'BlogPost', 'id', 'staff_member', 'blogPosts'],
            [true, 'StaffMember', 'BlogPost', 'id', 'author', 'blog_posts_where_author'],
            [true, 'StaffMember', 'BlogPost', 'id', 'staff_member', 'blog_posts'],
            // same table reference
            [false, 'StaffMember', 'StaffMember', 'id', 'staffMemberId', 'staffMembers'],
            [false, 'StaffMember', 'StaffMember', 'id', 'lineManagerId', 'staffMembersWhereLineManager'],
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
            [false, 'StaffMember', 'BlogPost', 'blogPosts'],
            [true, 'StaffMember', 'BlogPost', 'blog_posts'],
            // Same table reference
            [false, 'StaffMember', 'StaffMember', 'staffMembers']
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
