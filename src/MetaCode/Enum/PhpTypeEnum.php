<?php

namespace Reliese\MetaCode\Enum;

use RuntimeException;

/**
 * Class PhpType
 */
class PhpTypeEnum
{
    protected const STRING_TYPE_ID = 0;

    protected const INT_TYPE_ID = 10;

    protected const FLOAT_TYPE_ID = 20;
    
    protected const BOOL_TYPE_ID = 30;

    protected const ARRAY_TYPE_ID = 40;

    protected const OBJECT_TYPE_ID = 50;

    protected const STATIC_TYPE_ID = 60;

    private static ?PhpTypeEnum $stringTypeInstance = null;
    private static ?PhpTypeEnum $intTypeInstance = null;
    private static ?PhpTypeEnum $floatTypeInstance = null;
    private static ?PhpTypeEnum $boolTypeInstance = null;
    private static array $arrayTypeInstances = [];
    private static array $objectTypeInstance = [];
    private static ?PhpTypeEnum $staticTypeInstance = null;

    /**
     * Only used for Array Type
     * @var string|null 
     */
    private ?string $containedTypeName = null;

    /**
     * Only used for Object Type
     * @var string|null
     */
    private ?string $fullyQualifiedObjectClassName = null;

    /**
     * @var int 
     */
    private int $phpTypeId;

    private function __construct($phpTypeId)
    {
        $this->phpTypeId = $phpTypeId;
    }

    public function isString(): bool
    {
        return static::STRING_TYPE_ID === $this->phpTypeId;
    }

    public function isInt(): bool
    {
        return static::INT_TYPE_ID === $this->phpTypeId;
    }

    public function isFloat(): bool
    {
        return static::FLOAT_TYPE_ID === $this->phpTypeId;
    }

    public function isBool(): bool
    {
        return static::BOOL_TYPE_ID === $this->phpTypeId;
    }

    public function isArrayOfType(string $containedTypeName): bool
    {
        if (!$this->isArray()) {
            return false;
        }

        return $containedTypeName === $this->containedTypeName;
    }

    public function isArray(): bool
    {
        return static::ARRAY_TYPE_ID === $this->phpTypeId;
    }

    public function isObjectOfType(string $fullyQualifiedObjectClassName): bool
    {
        if (!$this->isObject()) {
            return false;
        }

        return $fullyQualifiedObjectClassName === $this->fullyQualifiedObjectClassName;
    }

    public function isObject(): bool
    {
        return static::OBJECT_TYPE_ID === $this->phpTypeId;
    }

    public function isStatic(): bool
    {
        return static::STATIC_TYPE_ID === $this->phpTypeId;
    }

    public static function stringType(): PhpTypeEnum
    {
        if (static::$stringTypeInstance) {
            return static::$stringTypeInstance;
        }
        return static::$stringTypeInstance = new static(static::STRING_TYPE_ID);
    }

    public static function intType(): PhpTypeEnum
    {
        if (static::$intTypeInstance) {
            return static::$intTypeInstance;
        }
        return static::$intTypeInstance = new static(static::INT_TYPE_ID);
    }

    public static function floatType(): PhpTypeEnum
    {
        if (static::$floatTypeInstance) {
            return static::$floatTypeInstance;
        }
        return static::$floatTypeInstance = new static(static::FLOAT_TYPE_ID);
    }

    public static function boolType(): PhpTypeEnum
    {
        if (static::$boolTypeInstance) {
            return static::$boolTypeInstance;
        }
        return static::$boolTypeInstance = new static(static::BOOL_TYPE_ID);
    }

    public static function arrayType(string $containedTypeName): PhpTypeEnum
    {
        if (\array_key_exists($containedTypeName, static::$arrayTypeInstances)) {
            return static::$arrayTypeInstances[$containedTypeName];
        }
        return static::$arrayTypeInstances[$containedTypeName] = (new static(static::ARRAY_TYPE_ID))
            ->setContainedTypeName($containedTypeName);
    }

    public static function objectType(string $fullyQualifiedClassNameOfObjectType): PhpTypeEnum
    {
        if (\array_key_exists($fullyQualifiedClassNameOfObjectType, static::$objectTypeInstance)) {
            return static::$objectTypeInstance[$fullyQualifiedClassNameOfObjectType];
        }
        return static::$objectTypeInstance[$fullyQualifiedClassNameOfObjectType]
            = (new static(static::OBJECT_TYPE_ID))->setObjectClassType($fullyQualifiedClassNameOfObjectType);
    }

    public static function staticTypeEnum(): PhpTypeEnum
    {
        if (static::$staticTypeInstance) {
            return static::$staticTypeInstance;
        }
        return static::$staticTypeInstance = new static(static::STATIC_TYPE_ID);
    }

    public function toDeclarationType() : string
    {
        if (static::isString()) {
            return 'string';
        }

        if (static::INT_TYPE_ID === $this->phpTypeId) {
            return 'int';
        }

        if (static::FLOAT_TYPE_ID === $this->phpTypeId) {
            return 'float';
        }

        if (static::BOOL_TYPE_ID === $this->phpTypeId) {
            return 'bool';
        }

        if (static::ARRAY_TYPE_ID === $this->phpTypeId) {
            return 'array';
        }

        if (static::OBJECT_TYPE_ID === $this->phpTypeId) {
            return $this->fullyQualifiedObjectClassName;
        }

        if (static::STATIC_TYPE_ID === $this->phpTypeId) {
            return 'static';
        }

        throw new RuntimeException(__METHOD__." Died because ".__CLASS__." was misused.");
    }

    public function toAnnotationTypeString() : string
    {
        if (static::isString()) {
            return 'string';
        }

        if (static::INT_TYPE_ID === $this->phpTypeId) {
            return 'int';
        }

        if (static::FLOAT_TYPE_ID === $this->phpTypeId) {
            return 'float';
        }

        if (static::BOOL_TYPE_ID === $this->phpTypeId) {
            return 'bool';
        }

        if (static::ARRAY_TYPE_ID === $this->phpTypeId) {
            return $this->containedTypeName.'[]';
        }

        if (static::OBJECT_TYPE_ID === $this->phpTypeId) {
            return $this->fullyQualifiedObjectClassName;
        }

        if (static::STATIC_TYPE_ID === $this->phpTypeId) {
            return 'static';
        }

        throw new RuntimeException(__METHOD__." Died because ".__CLASS__." was misused.");
    }

    public function __toString(): string
    {
        if (static::isString()) {
            return 'string';
        }

        return __METHOD__.' failed';
    }

    private function setContainedTypeName(string $containedTypeName) : PhpTypeEnum
    {
        $this->containedTypeName = $containedTypeName;
        return $this;
    }

    private function setObjectClassType(string $fullyQualifiedObjectClassName) : PhpTypeEnum
    {
        $this->fullyQualifiedObjectClassName = $fullyQualifiedObjectClassName;
        return $this;
    }
}
