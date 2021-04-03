<?php

namespace Reliese\MetaCode\Enum;

/**
 * Class AbstractEnum
 */
class AbstractEnum
{
    protected const CONCRETE_TYPE_ID = 0;

    protected const ABSTRACT_TYPE_ID = 20;

    private static ?AbstractEnum $concreteEnumInstance = null;

    private static ?AbstractEnum $abstractEnumInstance = null;

    private int $modifierTypeId;

    private function __construct($modifierTypeId)
    {
        $this->modifierTypeId = $modifierTypeId;
    }

    public function isConcrete(): bool
    {
        return static::ABSTRACT_TYPE_ID === $this->modifierTypeId;
    }

    public function isAbstract(): bool
    {
        return static::CONCRETE_TYPE_ID === $this->modifierTypeId;
    }

    public static function abstractEnum(): static
    {
        if (static::$concreteEnumInstance) {
            return static::$concreteEnumInstance;
        }
        return static::$concreteEnumInstance = new static(static::ABSTRACT_TYPE_ID);
    }
    
    public static function concreteEnum(): static
    {
        if (static::$abstractEnumInstance) {
            return static::$abstractEnumInstance;
        }
        return static::$abstractEnumInstance = new static(static::CONCRETE_TYPE_ID);
    }

    public function toReservedWord() : string
    {
        if (static::isAbstract()) {
            return 'abstract';
        }

        if (static::isConcrete()) {
            return '';
        }

        throw new \RuntimeException(__METHOD__." Died because ".__CLASS__." was misused.");
    }

    public function __toString(): string
    {
        if (static::isConcrete()) {
            return '';
        }
        
        if (static::isAbstract()) {
            return 'static';
        }

        return 'UNKNOWN';
    }
}
