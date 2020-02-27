<?php

namespace Reliese\Tests\Support;

class DumperTest extends \PHPUnit\Framework\TestCase
{
    public function testExportArray()
    {
        $testValue = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $result = \Reliese\Support\Dumper::export($testValue);

        $this->assertEquals("[\n\t\t'key1' => 'value1',\n\t\t'key2' => 'value2'\n\t]", $result);
    }

    public function testExportArrayWithoutKeys()
    {
        $testValue = [
            'value1',
            'value2'
        ];

        $result = \Reliese\Support\Dumper::export($testValue);

        $this->assertEquals("[\n\t\t'value1',\n\t\t'value2'\n\t]", $result);
    }

    public function testExportArrayWithNumericalKeys()
    {
        $testValue = [
            '1' => 'value1',
            '2' => 'value2'
        ];

        $result = \Reliese\Support\Dumper::export($testValue);

        $this->assertEquals("[\n\t\t'value1',\n\t\t'value2'\n\t]", $result);
    }

    public function testExportArrayWithStaticCallsInKey()
    {
        $testValue = [
            'DB::set' => 'value1',
            'Eloquent::data' => 'value2'
        ];

        $result = \Reliese\Support\Dumper::export($testValue);

        $this->assertEquals("[\n\t\tDB::set => 'value1',\n\t\tEloquent::data => 'value2'\n\t]", $result);
    }

    public function testExportArrayWithStaticCallsInValue()
    {
        $testValue = [
            'key1' => 'DB::set',
            'key2' => 'Eloquent::data'
        ];

        $result = \Reliese\Support\Dumper::export($testValue);

        $this->assertEquals("[\n\t\t'key1' => DB::set,\n\t\t'key2' => Eloquent::data\n\t]", $result);
    }

    public function testExportString()
    {
        $testValue = 'This is a test String';

        $result = \Reliese\Support\Dumper::export($testValue);

        $this->assertEquals("'This is a test String'", $result);
    }

    public function testExportStringWithStaticCall()
    {
        $testValue = 'DB::set';

        $result = \Reliese\Support\Dumper::export($testValue);

        $this->assertEquals('DB::set', $result);
    }

    public function testExportArbitraryValue()
    {
        $testValue = 123451.634324;

        $result = \Reliese\Support\Dumper::export($testValue);

        $this->assertEquals('123451.634324', $result);
    }
}
