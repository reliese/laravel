<?php

namespace Reliese\MetaCode\Enum;

use Illuminate\Support\Str;
use Reliese\MetaCode\Definition\ObjectTypeDefinition;
use Reliese\MetaCode\Tool\ClassNameTool;
use RuntimeException;
use function floatval;
use function in_array;
use function is_null;
use function number_format;
use function sprintf;
use function trim;

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

    protected const NOT_DEFINED_TYPE_ID = 80;

    const MIXED_TYPE_ID = 90;

    /**
     * @var PhpTypeEnum
     */
    private static ?PhpTypeEnum $notDefined = null;

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

    private static ?PhpTypeEnum $mixedTypeInstance = null;

    private static ?array $numericTypes = null;

    public static function getNumericTypes() : array {
        return static::$numericTypes ??= [
            self::INT_TYPE_ID,
            self::NULLABLE_INT_TYPE_ID,
            self::FLOAT_TYPE_ID,
            self::NULLABLE_FLOAT_TYPE_ID,
        ];
    }
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

    private function __construct($phpTypeId, $isNullable)
    {
        $this->phpTypeId = $phpTypeId;
        $this->isNullable = $isNullable;
    }

    public static function notDefined(): PhpTypeEnum
    {
        if (static::$notDefined) {
            return static::$notDefined;
        }
        return static::$notDefined = new static(static::NOT_DEFINED_TYPE_ID, true);
    }

    public static function mixedType()
    {
        if (static::$mixedTypeInstance) {
            return static::$mixedTypeInstance;
        }
        return static::$mixedTypeInstance = new static(static::MIXED_TYPE_ID, false);
    }

    public function isDefined(): bool
    {
        return static::NOT_DEFINED_TYPE_ID !== $this->phpTypeId;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    public function isMixed(): bool
    {
        return static::MIXED_TYPE_ID === $this->phpTypeId;
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
        return static::$stringTypeInstance = new static(static::STRING_TYPE_ID, false);
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
        return static::$intTypeInstance = new static(static::INT_TYPE_ID, false);
    }

    public static function nullableIntType(): PhpTypeEnum
    {
        if (static::$nullableIntTypeInstance) {
            return static::$nullableIntTypeInstance;
        }
        return static::$nullableIntTypeInstance = new static(static::NULLABLE_INT_TYPE_ID, true);
    }

    public static function floatType(): PhpTypeEnum
    {
        if (static::$floatTypeInstance) {
            return static::$floatTypeInstance;
        }
        return static::$floatTypeInstance = new static(static::FLOAT_TYPE_ID, false);
    }

    public static function nullableFloatType(): PhpTypeEnum
    {
        if (static::$nullableFloatTypeInstance) {
            return static::$nullableFloatTypeInstance;
        }
        return static::$nullableFloatTypeInstance = new static(static::NULLABLE_FLOAT_TYPE_ID, true);
    }

    public static function boolType(): PhpTypeEnum
    {
        if (static::$boolTypeInstance) {
            return static::$boolTypeInstance;
        }
        return static::$boolTypeInstance = new static(static::BOOL_TYPE_ID, false);
    }

    public static function nullableBoolType(): PhpTypeEnum
    {
        if (static::$nullableBoolTypeInstance) {
            return static::$nullableBoolTypeInstance;
        }
        return static::$nullableBoolTypeInstance = new static(static::NULLABLE_BOOL_TYPE_ID, true);
    }

    public static function arrayType(string $containedTypeName): PhpTypeEnum
    {
        if (\array_key_exists($containedTypeName, static::$arrayTypeInstances)) {
            return static::$arrayTypeInstances[$containedTypeName];
        }
        return static::$arrayTypeInstances[$containedTypeName] = (new static(static::ARRAY_TYPE_ID, false))
            ->setContainedTypeName($containedTypeName);
    }

    public static function nullableArrayType(string $containedTypeName): PhpTypeEnum
    {
        if (\array_key_exists($containedTypeName, static::$nullableArrayTypeInstances)) {
            return static::$nullableArrayTypeInstances[$containedTypeName];
        }
        return static::$nullableArrayTypeInstances[$containedTypeName] = (new static(static::NULLABLE_ARRAY_TYPE_ID, true))
            ->setContainedTypeName($containedTypeName);
    }

    public static function objectOfType(string $fullyQualifiedClassNameOfObjectType): PhpTypeEnum
    {
        if (\array_key_exists($fullyQualifiedClassNameOfObjectType, static::$objectTypeInstance)) {
            return static::$objectTypeInstance[$fullyQualifiedClassNameOfObjectType];
        }
        return static::$objectTypeInstance[$fullyQualifiedClassNameOfObjectType]
            = (new static(static::OBJECT_TYPE_ID, false))->setObjectClassType($fullyQualifiedClassNameOfObjectType);
    }

    public static function nullableObjectOfType(string $fullyQualifiedClassNameOfObjectType): PhpTypeEnum
    {
        if (\array_key_exists($fullyQualifiedClassNameOfObjectType, static::$nullableObjectTypeInstance)) {
            return static::$nullableObjectTypeInstance[$fullyQualifiedClassNameOfObjectType];
        }
        return static::$nullableObjectTypeInstance[$fullyQualifiedClassNameOfObjectType]
            = (new static(static::NULLABLE_OBJECT_TYPE_ID, true))->setObjectClassType($fullyQualifiedClassNameOfObjectType);
    }

    public static function staticTypeEnum(): PhpTypeEnum
    {
        if (static::$staticTypeInstance) {
            return static::$staticTypeInstance;
        }
        return static::$staticTypeInstance = new static(static::STATIC_TYPE_ID, false);
    }

    public static function nullableStaticTypeEnum(): PhpTypeEnum
    {
        if (static::$nullableStaticTypeInstance) {
            return static::$nullableStaticTypeInstance;
        }
        return static::$nullableStaticTypeInstance = new static(static::NULLABLE_STATIC_TYPE_ID, true);
    }

    public function toDeclarationType() : string
    {
        if (static::MIXED_TYPE_ID === $this->phpTypeId) {
            return 'mixed';
        }

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

        if (static::NOT_DEFINED_TYPE_ID === $this->phpTypeId) {
            return '';
        }

        throw new RuntimeException(__METHOD__." Died because ".__CLASS__." was misused.");
    }

    public function toAnnotationTypeString() : string
    {
        if (static::isMixed()) {
            return 'mixed';
        }

        if (static::STRING_TYPE_ID === $this->phpTypeId) {
            return 'string';
        }

        if (static::NULLABLE_STRING_TYPE_ID === $this->phpTypeId) {
            return 'null|string';
        }

        if (static::INT_TYPE_ID === $this->phpTypeId) {
            return 'int';
        }

        if (static::NULLABLE_INT_TYPE_ID === $this->phpTypeId) {
            return 'null|int';
        }

        if (static::FLOAT_TYPE_ID === $this->phpTypeId) {
            return 'float';
        }

        if (static::NULLABLE_FLOAT_TYPE_ID === $this->phpTypeId) {
            return 'null|float';
        }

        if (static::BOOL_TYPE_ID === $this->phpTypeId) {
            return 'bool';
        }

        if (static::NULLABLE_BOOL_TYPE_ID === $this->phpTypeId) {
            return 'null|bool';
        }

        if (static::ARRAY_TYPE_ID === $this->phpTypeId) {
            return $this->containedTypeName.'[]';
        }

        if (static::NULLABLE_ARRAY_TYPE_ID === $this->phpTypeId) {
            return 'null|'.$this->containedTypeName.'[]';
        }

        if (static::OBJECT_TYPE_ID === $this->phpTypeId) {
            return $this->fullyQualifiedObjectClassName;
        }

        if (static::NULLABLE_OBJECT_TYPE_ID === $this->phpTypeId) {
            return 'null|'.$this->fullyQualifiedObjectClassName;
        }

        if (static::STATIC_TYPE_ID === $this->phpTypeId) {
            return 'static';
        }

        if (static::NULLABLE_STATIC_TYPE_ID === $this->phpTypeId) {
            return 'null|static';
        }

        if (static::NOT_DEFINED_TYPE_ID === $this->phpTypeId) {
            return '';
        }

        throw new RuntimeException(__METHOD__." Died because ".__CLASS__." was misused.");
    }

    public function hasFullyQualifiedObjectClassName(): bool
    {
        return !empty($this->fullyQualifiedObjectClassName);
    }

    public function getFullyQualifiedObjectClassName(): ?string
    {
        return '\\'.trim($this->fullyQualifiedObjectClassName, '\\');
    }

    public function hasTypeTest(): bool
    {
        switch ($this->phpTypeId) {
            case static::MIXED_TYPE_ID:
            case static::NOT_DEFINED_TYPE_ID:
                return false;

            case static::STRING_TYPE_ID:
            case static::NULLABLE_STRING_TYPE_ID:
                return true;

            case static::INT_TYPE_ID:
            case static::NULLABLE_INT_TYPE_ID:
                return true;

            case static::FLOAT_TYPE_ID:
            case static::NULLABLE_FLOAT_TYPE_ID:
                return true;

            case static::BOOL_TYPE_ID:
            case static::NULLABLE_BOOL_TYPE_ID:
                return true;

            case static::ARRAY_TYPE_ID:
            case static::NULLABLE_ARRAY_TYPE_ID:
                return true;

            case static::OBJECT_TYPE_ID:
            case static::NULLABLE_OBJECT_TYPE_ID:
                return true;

            case static::STATIC_TYPE_ID:
            case static::NULLABLE_STATIC_TYPE_ID:
                return true;
        }

        return false;
    }

    public function toObjectTypeDefinition(): ObjectTypeDefinition
    {
        if (empty($this->fullyQualifiedObjectClassName)) {
            throw new \RuntimeException(__METHOD__." failed because it was called on a non-object type.");
        }
        return new ObjectTypeDefinition($this->getFullyQualifiedObjectClassName());
    }

    public function toTypeHint(): string
    {
        if ($this->isImportable()) {
            return ($this->isNullable() ? '?' : '') . $this->toObjectTypeDefinition()->getImportableName();
        }
        return $this->toDeclarationType();
    }

    public function toVariableTypeTest(string $valueStatement, bool $invertResult = false): string
    {
        switch ($this->phpTypeId) {
            case static::MIXED_TYPE_ID:
            case static::NOT_DEFINED_TYPE_ID:
                throw new RuntimeException("variables of type 'mixed' cannot perform type tests");

            case static::STRING_TYPE_ID:
            case static::NULLABLE_STRING_TYPE_ID:
                if ($invertResult) {
                    return sprintf("!\\is_string(%s)", $valueStatement);
                }
                return sprintf("\\is_string(%s)", $valueStatement);

            case static::INT_TYPE_ID:
            case static::NULLABLE_INT_TYPE_ID:
                if ($invertResult) {
                    return sprintf("!\\is_int(%s)", $valueStatement);
                }
                return sprintf("\\is_int(%s)", $valueStatement);

            case static::FLOAT_TYPE_ID:
            case static::NULLABLE_FLOAT_TYPE_ID:
                if ($invertResult) {
                    return sprintf("!\\is_float(%s)", $valueStatement);
                }
                return sprintf("\\is_float(%s)", $valueStatement);

            case static::BOOL_TYPE_ID:
            case static::NULLABLE_BOOL_TYPE_ID:
                if ($invertResult) {
                    return sprintf("!\\is_bool(%s)", $valueStatement);
                }
                return sprintf("\\is_bool(%s)", $valueStatement);

            case static::ARRAY_TYPE_ID:
            case static::NULLABLE_ARRAY_TYPE_ID:
                if ($invertResult) {
                    return sprintf('!\is_array(%s)', $valueStatement);
                }
                return sprintf('\is_array(%s)', $valueStatement);

            case static::OBJECT_TYPE_ID:
            case static::NULLABLE_OBJECT_TYPE_ID:
                $statement = sprintf(
                    "%s instanceof %s",
                    $valueStatement,
                    ClassNameTool::fullyQualifiedClassNameToClassName($this->fullyQualifiedObjectClassName)
                );
                if ($invertResult) {
                    return "!($statement)";
                }
                return $statement;

            case static::STATIC_TYPE_ID:
            case static::NULLABLE_STATIC_TYPE_ID:
                $statement = sprintf("%s instanceof static", $valueStatement);
                if ($invertResult) {
                    return "!($statement)";
                }
                return $statement;
        }

        throw new RuntimeException(__METHOD__." Died because ".__CLASS__." was misused.");
    }

    public function toUpperSnakeCase(): string
    {
        switch ($this->phpTypeId) {
            case static::MIXED_TYPE_ID:
            case static::NOT_DEFINED_TYPE_ID:
                return 'MIXED';

            case static::STRING_TYPE_ID:
            case static::NULLABLE_STRING_TYPE_ID:
                return 'STRING';

            case static::INT_TYPE_ID:
            case static::NULLABLE_INT_TYPE_ID:
                return 'INT';

            case static::FLOAT_TYPE_ID:
            case static::NULLABLE_FLOAT_TYPE_ID:
                return 'FLOAT';

            case static::BOOL_TYPE_ID:
            case static::NULLABLE_BOOL_TYPE_ID:
                return 'BOOL';

            case static::ARRAY_TYPE_ID:
            case static::NULLABLE_ARRAY_TYPE_ID:
                return 'ARRAY';

            case static::OBJECT_TYPE_ID:
            case static::NULLABLE_OBJECT_TYPE_ID:
                $className = ClassNameTool::fullyQualifiedClassNameToClassName($this->fullyQualifiedObjectClassName);
                return Str::upper(Str::snake($className));

            case static::STATIC_TYPE_ID:
            case static::NULLABLE_STATIC_TYPE_ID:
                return 'STATIC';

            default:
                throw new RuntimeException(__METHOD__." failed for PhpTypeId: {$this->phpTypeId}");
        }
    }

    public function isNumeric(): bool
    {
        return in_array($this->phpTypeId, static::getNumericTypes());
    }

    public function toNumericCast(?string $value) : string
    {
        if (is_null($value)) {
            return 'null';
        }

        switch ($this->phpTypeId) {

            case static::INT_TYPE_ID:
            case static::NULLABLE_INT_TYPE_ID:
                return number_format($value, 0,'','');

            case static::FLOAT_TYPE_ID:
            case static::NULLABLE_FLOAT_TYPE_ID:
                return sprintf('%f', $value);

            default:
                throw new \RuntimeException(__METHOD__." failed");
        }
    }

    public function isImportable()
    {
        return $this->isObject() || $this->isNullableObject();
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
