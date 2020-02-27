<?php

namespace Reliese\Tests\Support;

use PHPUnit\Framework\TestCase;
use Reliese\Support\Classify;

class ClassifyTest extends
    TestCase
{
    public function testConstant(): void
    {
        $classify = new Classify();

        $this->assertEquals("\n * @test int", $classify->annotation('test', 'int'));
    }

    public function testAnnotation(): void
    {
        $classify = new Classify();

        $this->assertEquals("\tconst test = DB::test;\n", $classify->constant('test', 'DB::test'));
        $this->assertEquals("\tconst test = '2';\n", $classify->constant('test', '2'));
        $this->assertEquals("\tconst test = 'test';\n", $classify->constant('test', 'test'));
    }

    public function testField(): void
    {
        $classify = new Classify();

        $this->assertEquals("\tprotected \$field1 = 2;\n", $classify->field('field1', 2));
        $this->assertEquals("\tprivate \$field1 = 2;\n", $classify->field('field1', 2, [
            'visibility' => 'private'
        ]));

        $this->assertEquals("\t\t\tprotected \$field1 = 2;\n", $classify->field('field1', 2, [
            'before' => "\t\t"
        ]));

        $this->assertEquals("\tprotected \$field1 = 2;\t\t", $classify->field('field1', 2, [
            'after' => "\t\t"
        ]));
    }

    public function testMixin(): void
    {
        $classify = new Classify();

        $this->assertEquals("\tuse \\Test\\Support;\n", $classify->mixin('\\Test\\Support'));
        $this->assertEquals("\tuse \\Test\\Support;\n", $classify->mixin('Test\\Support'));
    }

    public function testMethod(): void
    {
        $classify = new Classify();

        $this->assertEquals("\n\tpublic function methodName1()\n\t{\n\t\t//this is some code\necho '123';\n\t}\n", $classify->method('methodName1', "//this is some code\necho '123';"));
        $this->assertEquals("\n\tprivate function methodName1()\n\t{\n\t\t//this is some code\necho '123';\n\t}\n", $classify->method('methodName1', "//this is some code\necho '123';", [
            'visibility' => 'private'
        ]));
    }
}
