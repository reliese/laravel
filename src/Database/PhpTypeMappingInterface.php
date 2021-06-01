<?php

namespace Reliese\Database;

use Reliese\MetaCode\Enum\PhpTypeEnum;
interface PhpTypeMappingInterface
{
    /**
     * @param string $dbTypeName
     * @param int $length
     * @param int $precision
     * @param int $scale
     * @param bool $isNullable
     *
     * @return PhpTypeEnum
     */
    public function getPhpTypeEnumFromDatabaseType(
        string $dbTypeName,
        int $length,
        int $precision,
        int $scale,
        bool $isNullable
    );
}