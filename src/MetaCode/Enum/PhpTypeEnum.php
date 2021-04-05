<?php

namespace Reliese\MetaCode\Enum;

use RuntimeException;

/**
 * Class PhpType
 */
class PhpTypeEnum
{
    protected const STRING_TYPE_ID = 10;
    protected const NULLABLE_STRING_TYPE_ID = 15;

    protected const INT_TYPE_ID = 20;
    protected const NULLABLE_INT_TYPE_ID = 25;

    protected const FLOAT_TYPE_ID = 30;
    protected const NULLABLE_FLOAT_TYPE_ID = 35;
    
    protected const BOOL_TYPE_ID = 40;
    protected const NULLABLE_BOOL_TYPE_ID = 45;

    protected const ARRAY_TYPE_ID = 50;
    protected const NULLABLE_ARRAY_TYPE_ID = 55;

    protected const OBJECT_TYPE_ID = 60;
    protected const NULLABLE_OBJECT_TYPE_ID = 65;

    protected const STATIC_TYPE_ID = 70;
    protected const NULLABLE_STATIC_TYPE_ID = 75;

    private static ?PhpTypeEnum $stringTypeInstance = null;
    private static ?PhpTypeEnum $nullableStringTypeInstance = null;

    private static ?PhpTypeEnum $intTypeInstance = null;
    private static ?PhpTypeEnum $nullableIntTypeInstance = null;

    private static ?PhpTypeEnum $floatTypeInstance = null;
    private static ?PhpTypeEnum $nullableFloatTypeInstance = null;

    private static ?PhpTypeEnum $boolTypeInstance = null;
    private static ?PhpTypeEnum $nullableBoolTypeInstance = null;

    private static array $arrayTypeInstances = [];
    private static array $nullableArrayTypeInstances = [];

    private static array $objectTypeInstance = [];
    private static array $nullableObjectTypeInstance = [];

    private static ?PhpTypeEnum $staticTypeInstance = null;
    private static ?PhpTypeEnum $nullableStaticTypeInstance = null;

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

    /**
     * @var bool
     */
    private bool $isNullable = false;

    private function __construct($phpTypeId, $isNullable = false)
    {
        $this->phpTypeId = $phpTypeId;
        $this->isNullable = $isNullable;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    public function isString(): bool
    {
        return static::STRING_TYPE_ID === $this->phpTypeId;
    }

    public function isNullableString(): bool
    {
        return static::NULLABLE_STRING_TYPE_ID === $this->phpTypeId;
    }

    public function isInt(): bool
    {
        return static::INT_TYPE_ID === $this->phpTypeId;
    }

    public function isNullableInt(): bool
    {
        return static::NULLABLE_INT_TYPE_ID === $this->phpTypeId;
    }

    public function isFloat(): bool
    {
        return static::FLOAT_TYPE_ID === $this->phpTypeId;
    }

    public function isNullableFloat(): bool
    {
        return static::NULLABLE_FLOAT_TYPE_ID === $this->phpTypeId;
    }

    public function isBool(): bool
    {
        return static::BOOL_TYPE_ID === $this->phpTypeId;
    }

    public function isNullableBool(): bool
    {
        return static::NULLABLE_BOOL_TYPE_ID === $this->phpTypeId;
    }

    public function isArrayOfType(string $containedTypeName): bool
    {
        if (!$this->isArray()) {
            return false;
        }

        return $containedTypeName === $this->containedTypeName;
    }

    public function isNullableArrayOfType(string $containedTypeName): bool
    {
        if (!$this->isNullableArray()) {
            return false;
        }

        return $containedTypeName === $this->containedTypeName;
    }

    public function isArray(): bool
    {
        return static::ARRAY_TYPE_ID === $this->phpTypeId;
    }

    public function isNullableArray(): bool
    {
        return static::NULLABLE_ARRAY_TYPE_ID === $this->phpTypeId;
    }

    public function isObjectOfType(string $fullyQualifiedObjectClassName): bool
    {
        if (!$this->isObject()) {
            return false;
        }

        return $fullyQualifiedObjectClassName === $this->fullyQualifiedObjectClassName;
    }

    public function isNullableObjectOfType(string $fullyQualifiedObjectClassName): bool
    {
        if (!$this->isNullableObject()) {
            return false;
        }

        return $fullyQualifiedObjectClassName === $this->fullyQualifiedObjectClassName;
    }

    public function isObject(): bool
    {
        return static::OBJECT_TYPE_ID === $this->phpTypeId;
    }

    public function isNullableObject(): bool
    {
        return static::NULLABLE_OBJECT_TYPE_ID === $this->phpTypeId;
    }

    public function isStatic(): bool
    {
        return static::STATIC_TYPE_ID === $this->phpTypeId;
    }

    public function isNullableStatic(): bool
    {
        return static::NULLABLE_STATIC_TYPE_ID === $this->phpTypeId;
    }

    public static function stringType(): PhpTypeEnum
    {
        if (static::$stringTypeInstance) {
            return static::$stringTypeInstance;
        }
        return static::$stringTypeInstance = new static(static::STRING_TYPE_ID);
    }

    public static function nullableStringType(): PhpTypeEnum
    {
        if (static::$nullableStringTypeInstance) {
            return static::$nullableStringTypeInstance;
        }
        return static::$nullableStringTypeInstance = new static(static::NULLABLE_STRING_TYPE_ID, true);
    }

    public static function intType(): PhpTypeEnum
    {
        if (static::$intTypeInstance) {
            return static::$intTypeInstance;
        }
        return static::$intTypeInstance = new static(static::INT_TYPE_ID);
    }

    public static function nullableIntType(): PhpTypeEnum
    {
        if (static::$nullableIntTypeInstance) {
            return static::$nullableIntTypeInstance;
        }
        return static::$nullableIntTypeInstance = new static(static::NULLABLE_INT_TYPE_ID);
    }

    public static function floatType(): PhpTypeEnum
    {
        if (static::$floatTypeInstance) {
            return static::$floatTypeInstance;
        }
        return static::$floatTypeInstance = new static(static::FLOAT_TYPE_ID);
    }

    public static function nullableFloatType(): PhpTypeEnum
    {
        if (static::$nullableFloatTypeInstance) {
            return static::$nullableFloatTypeInstance;
        }
        return static::$nullableFloatTypeInstance = new static(static::NULLABLE_FLOAT_TYPE_ID);
    }

    public static function boolType(): PhpTypeEnum
    {
        if (static::$boolTypeInstance) {
            return static::$boolTypeInstance;
        }
        return static::$boolTypeInstance = new static(static::BOOL_TYPE_ID);
    }

    public static function nullableBoolType(): PhpTypeEnum
    {
        if (static::$nullableBoolTypeInstance) {
            return static::$nullableBoolTypeInstance;
        }
        return static::$nullableBoolTypeInstance = new static(static::NULLABLE_BOOL_TYPE_ID);
    }

    public static function arrayType(string $containedTypeName): PhpTypeEnum
    {
        if (\array_key_exists($containedTypeName, static::$arrayTypeInstances)) {
            return static::$arrayTypeInstances[$containedTypeName];
        }
        return static::$arrayTypeInstances[$containedTypeName] = (new static(static::ARRAY_TYPE_ID))
            ->setContainedTypeName($containedTypeName);
    }

    public static function nullableArrayType(string $containedTypeName): PhpTypeEnum
    {
        if (\array_key_exists($containedTypeName, static::$nullableArrayTypeInstances)) {
            return static::$nullableArrayTypeInstances[$containedTypeName];
        }
        return static::$nullableArrayTypeInstances[$containedTypeName] = (new static(static::NULLABLE_ARRAY_TYPE_ID))
            ->setContainedTypeName($containedTypeName);
    }

    public static function objectOfType(string $fullyQualifiedClassNameOfObjectType): PhpTypeEnum
    {
        if (\array_key_exists($fullyQualifiedClassNameOfObjectType, static::$objectTypeInstance)) {
            return static::$objectTypeInstance[$fullyQualifiedClassNameOfObjectType];
        }
        return static::$objectTypeInstance[$fullyQualifiedClassNameOfObjectType]
            = (new static(static::OBJECT_TYPE_ID))->setObjectClassType($fullyQualifiedClassNameOfObjectType);
    }

    public static function nullableObjectOfType(string $fullyQualifiedClassNameOfObjectType): PhpTypeEnum
    {
        if (\array_key_exists($fullyQualifiedClassNameOfObjectType, static::$nullableObjectTypeInstance)) {
            return static::$nullableObjectTypeInstance[$fullyQualifiedClassNameOfObjectType];
        }
        return static::$nullableObjectTypeInstance[$fullyQualifiedClassNameOfObjectType]
            = (new static(static::NULLABLE_OBJECT_TYPE_ID))->setObjectClassType($fullyQualifiedClassNameOfObjectType);
    }

    public static function staticTypeEnum(): PhpTypeEnum
    {
        if (static::$staticTypeInstance) {
            return static::$staticTypeInstance;
        }
        return static::$staticTypeInstance = new static(static::STATIC_TYPE_ID);
    }

    public static function nullableStaticTypeEnum(): PhpTypeEnum
    {
        if (static::$nullableStaticTypeInstance) {
            return static::$nullableStaticTypeInstance;
        }
        return static::$nullableStaticTypeInstance = new static(static::NULLABLE_STATIC_TYPE_ID);
    }

    public function toDeclarationType() : string
    {
        if (static::STRING_TYPE_ID === $this->phpTypeId) {
            return 'string';
        }

        if (static::NULLABLE_STRING_TYPE_ID === $this->phpTypeId) {
            return '?string';
        }

        if (static::INT_TYPE_ID === $this->phpTypeId) {
            return 'int';
        }

        if (static::NULLABLE_INT_TYPE_ID === $this->phpTypeId) {
            return '?int';
        }

        if (static::FLOAT_TYPE_ID === $this->phpTypeId) {
            return 'float';
        }

        if (static::NULLABLE_FLOAT_TYPE_ID === $this->phpTypeId) {
            return '?float';
        }

        if (static::BOOL_TYPE_ID === $this->phpTypeId) {
            return 'bool';
        }

        if (static::NULLABLE_BOOL_TYPE_ID === $this->phpTypeId) {
            return '?bool';
        }

        if (static::ARRAY_TYPE_ID === $this->phpTypeId) {
            return 'array';
        }

        if (static::NULLABLE_ARRAY_TYPE_ID === $this->phpTypeId) {
            return '?array';
        }

        if (static::OBJECT_TYPE_ID === $this->phpTypeId) {
            return $this->fullyQualifiedObjectClassName;
        }

        if (static::NULLABLE_OBJECT_TYPE_ID === $this->phpTypeId) {
            return '?'.$this->fullyQualifiedObjectClassName;
        }

        if (static::STATIC_TYPE_ID === $this->phpTypeId) {
            return 'static';
        }

        if (static::NULLABLE_STATIC_TYPE_ID === $this->phpTypeId) {
            return '?static';
        }

        throw new RuntimeException(__METHOD__." Died because ".__CLASS__." was misused.");
    }

    public function toAnnotationTypeString() : string
    {
        if (static::isString()) {
            return 'string';
        }

        if (static::isString()) {
            return 'nullable string';
        }

        if (static::INT_TYPE_ID === $this->phpTypeId) {
            return 'int';
        }

        if (static::INT_TYPE_ID === $this->phpTypeId) {
            return 'nullable int';
        }

        if (static::FLOAT_TYPE_ID === $this->phpTypeId) {
            return 'float';
        }

        if (static::FLOAT_TYPE_ID === $this->phpTypeId) {
            return 'nullable float';
        }

        if (static::BOOL_TYPE_ID === $this->phpTypeId) {
            return 'bool';
        }

        if (static::BOOL_TYPE_ID === $this->phpTypeId) {
            return 'nullable bool';
        }

        if (static::ARRAY_TYPE_ID === $this->phpTypeId) {
            return $this->containedTypeName.'[]';
        }

        if (static::ARRAY_TYPE_ID === $this->phpTypeId) {
            return 'nullable '.$this->containedTypeName.'[]';
        }

        if (static::OBJECT_TYPE_ID === $this->phpTypeId) {
            return $this->fullyQualifiedObjectClassName;
        }

        if (static::OBJECT_TYPE_ID === $this->phpTypeId) {
            return 'nullable '.$this->fullyQualifiedObjectClassName;
        }

        if (static::STATIC_TYPE_ID === $this->phpTypeId) {
            return 'static';
        }

        if (static::STATIC_TYPE_ID === $this->phpTypeId) {
            return 'nullable static';
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
