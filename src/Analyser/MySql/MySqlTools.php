<?php

namespace Reliese\Analyser\MySql;

use InvalidArgumentException;
/**
 * Class MySqlTools
 */
abstract class MySqlTools
{
    /**
     * converts "xyz" to "`xyz`"
     * converts "x.y.x" to "`x`.`y`.`z`"
     *
     * @param string $identifier
     *
     * @return string
     */
    public static function quoteIdentifier(string $identifier): string
    {
        $pieces = explode('.', str_replace('`', '', $identifier));

        return implode('.', array_map(function ($piece) {
            return "`$piece`";
        }, $pieces));
    }

    /**
     * @param $databaseDataType
     *
     * @return string
     */
    public static function getPhpDataTypeFromDatabaseDataType($databaseDataType)
    {
        switch (\strtolower($databaseDataType)) {
            case 'varchar' :
            case 'text' :
            case 'string' :
            case 'char' :
            case 'enum' :
            case 'tinytext' :
            case 'mediumtext' :
            case 'longtext' :
                return 'string';
            case 'datetime':
            case 'year':
            case 'date':
            case 'time':
            case 'timestamp':
                return 'date';
            case 'bigint':
            case 'int' :
            case 'integer' :
            case 'tinyint' :
            case 'smallint' :
            case 'mediumint' :
                return 'int';
            case 'float':
            case 'decimal':
            case 'numeric':
            case 'dec':
            case 'fixed':
            case 'double':
            case 'real':
            case 'double precision':
                return 'float';
            case 'longblob':
            case 'blob':
            case 'bit':
                return 'bool';
            default:
                throw new InvalidArgumentException("Unable to convert \"$databaseDataType\" to a php type");
        }
    }
}