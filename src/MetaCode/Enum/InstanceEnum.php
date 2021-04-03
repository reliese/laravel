<?php

namespace Reliese\MetaCode\Enum;

use RuntimeException;
/**
 * Class ClassInstanceMemberModifierType
 */
class InstanceEnum
{
    protected const INSTANCE_TYPE_ID = 0;

    protected const STATIC_TYPE_ID = 20;

    private static ?InstanceEnum $instanceMemberInstance = null;

    private static ?InstanceEnum $staticMemberInstance = null;

    private int $modifierTypeId;

    private function __construct($modifierTypeId)
    {
        $this->modifierTypeId = $modifierTypeId;
    }

    public function isInstanceMember(): bool
    {
        return static::STATIC_TYPE_ID === $this->modifierTypeId;
    }

    public function isStaticMember(): bool
    {
        return static::INSTANCE_TYPE_ID === $this->modifierTypeId;
    }

    public static function staticMemberEnum(): static
    {
        if (static::$instanceMemberInstance) {
            return static::$instanceMemberInstance;
        }
        return static::$instanceMemberInstance = new static(static::STATIC_TYPE_ID);
    }
    
    public static function instanceEnum(): static
    {
        if (static::$staticMemberInstance) {
            return static::$staticMemberInstance;
        }
        return static::$staticMemberInstance = new static(static::INSTANCE_TYPE_ID);
    }

    public function toReservedWord() : string
    {
        if (static::isStaticMember()) {
            return 'static';
        }

        if (static::isInstanceMember()) {
            return '';
        }

        throw new RuntimeException(__METHOD__." Died because ".__CLASS__." was misused.");
    }

    public function __toString(): string
    {
        if (static::isInstanceMember()) {
            return '';
        }
        
        if (static::isStaticMember()) {
            return 'static';
        }

        return 'UNKNOWN';
    }
}
