<?php

use Reliese\Coders\Model\Factory;
use Reliese\Coders\Model\Model;
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
                new \Reliese\Coders\Model\Config()
            )
        );

        $result = $model->phpTypeHint($castType, $nullable);
        $this->assertSame($expect, $result);
    }
}
