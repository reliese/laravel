<?php

use PHPUnit\Framework\TestCase;
use Reliese\Coders\Model\Relations\RelationHelper;

class RelationHelperTest extends TestCase
{
    public function provideKeys()
    {
        // usesSnakeAttributes, primaryKey, foreignKey, expected
        return [
            // camelCase
            [false, 'id', 'lineManagerId', 'lineManager'],
            [false, 'ID', 'lineManagerID', 'lineManager'],
            // snake_case
            [true, 'id', 'line_manager_id', 'line_manager'],
            [true, 'ID', 'line_manager_id', 'line_manager'],
            // no suffix
            [false, 'id', 'lineManager', 'lineManager'],
            [true, 'id', 'line_manager', 'line_manager'],
            // columns that contain the letters of the primary key as part of their name
            [false, 'id', 'holiday', 'holiday'],
            [true, 'id', 'something_identifier_id', 'something_identifier']
        ];
    }

    /**
     * @dataProvider provideKeys
     *
     * @param bool $usesSnakeAttributes
     * @param string $primaryKey
     * @param string $foreignKey
     * @param string $expected
     */
    public function testNameUsingForeignKeyStrategy($usesSnakeAttributes, $primaryKey, $foreignKey, $expected)
    {
        $this->assertEquals(
            $expected,
            RelationHelper::stripSuffixFromForeignKey($usesSnakeAttributes, $primaryKey, $foreignKey),
            json_encode(compact('usesSnakeAttributes', 'primaryKey', 'foreignKey'))
        );
    }
}
