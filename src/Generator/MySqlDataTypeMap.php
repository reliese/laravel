<?php

namespace Reliese\Generator;

use Reliese\MetaCode\Enum\PhpTypeEnum;
/**
 * Class DataTypeMap
 */
class MySqlDataTypeMap
{
    public function __construct()
    {
        $this->mappings = [

        ];
    }

    /**
     * @param string $dbTypeName
     * @param int $length
     * @param int $precision
     * @param int $scale
     * @param bool $isNullable
     *
     * @return PhpTypeEnum
     */
    public function getPhpTypeEnumFromDatabaseType(string $dbTypeName, int $length, int $precision, int $scale, bool $isNullable): PhpTypeEnum
    {
        switch ($dbTypeName) {
            case 'varchar':
            case 'text':
            case 'json': // TODO: Provide parsing option
            case 'string':
            case 'char':
            case 'enum':
            case 'tinytext':
            case 'mediumtext':
            case 'longtext':
                if ($isNullable) {
                    return PhpTypeEnum::nullableStringType();
                }
                return PhpTypeEnum::stringType();
            case 'datetime':
            case 'year':
            case 'date':
            case 'time':
            case 'timestamp':
                if ($isNullable) {
                    return PhpTypeEnum::nullableObjectOfType('\\'.\DateTime::class);
                }
                return PhpTypeEnum::objectOfType('\\'.\DateTime::class);
            case 'int':
            case 'integer':
            case 'smallint':
            case 'mediumint':
                if ($isNullable) {
                    return PhpTypeEnum::nullableIntType();
                }
                return PhpTypeEnum::intType();
            case 'tinyint':
                if ($length < 2) {
                    if ($isNullable) {
                        return PhpTypeEnum::nullableBoolType();
                    }
                    return PhpTypeEnum::boolType();
                }
                if ($isNullable) {
                    return PhpTypeEnum::nullableIntType();
                }
                return PhpTypeEnum::intType();
            case 'bigint':
            case 'float':
            case 'decimal':
            case 'numeric':
            case 'dec':
            case 'fixed':
            case 'double':
            case 'real':
            case 'double precision':
                if ($isNullable) {
                    return PhpTypeEnum::nullableFloatType();
                }
                return PhpTypeEnum::floatType();
            case 'longblob':
            case 'blob':
            case 'bit':
                if ($isNullable) {
                    return PhpTypeEnum::nullableArrayType('bool');
                }
                return PhpTypeEnum::arrayType('bool');
            case 'boolean':
                if ($isNullable) {
                    return PhpTypeEnum::nullableBoolType();
                }
                return PhpTypeEnum::boolType();
            default:
                throw new \InvalidArgumentException(__METHOD__." does not define a data type mapping for \"$dbTypeName\"");
        }
    }
}
