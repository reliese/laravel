<?php

namespace Reliese\MetaCode\Enum;

use RuntimeException;
/**
 * Class ClassMemberModifierEnum
 */
class VisibilityEnum
{
    protected const PRIVATE_TYPE_ID = 0;

    protected const PROTECTED_TYPE_ID = 10;

    protected const PUBLIC_TYPE_ID = 20;

    private static ?VisibilityEnum $publicMemberInstance = null;

    private static ?VisibilityEnum $privateMemberInstance = null;

    private static ?VisibilityEnum $protectedMemberInstance = null;

    private int $modifierTypeId;

    private function __construct($modifierTypeId)
    {
        $this->modifierTypeId = $modifierTypeId;
    }

    public function isPublic(): bool
    {
        return static::PUBLIC_TYPE_ID === $this->modifierTypeId;
    }

    public function isPrivate(): bool
    {
        return static::PRIVATE_TYPE_ID === $this->modifierTypeId;
    }

    public function isProtected(): bool
    {
        return static::PROTECTED_TYPE_ID === $this->modifierTypeId;
    }

    public static function publicEnum(): static
    {
        if (self::$publicMemberInstance) {
            return self::$publicMemberInstance;
        }
        return self::$publicMemberInstance = new static(static::PUBLIC_TYPE_ID);
    }

    public static function protectedEnum(): static
    {
        if (self::$protectedMemberInstance) {
            return self::$protectedMemberInstance;
        }
        return self::$protectedMemberInstance = new static(static::PROTECTED_TYPE_ID);
    }

    public static function privateEnum(): static
    {
        if (self::$privateMemberInstance) {
            return self::$privateMemberInstance;
        }
        return self::$privateMemberInstance = new VisibilityEnum(static::PRIVATE_TYPE_ID);
    }

    public function toReservedWord() : string
    {
        if (static::isPublic()) {
            return 'public';
        }

        if (static::isProtected()) {
            return 'protected';
        }

        if (static::isPrivate()) {
            return 'private';
        }

        throw new RuntimeException(__METHOD__." Died because ".__CLASS__." was misused.");
    }

    public function __toString(): string
    {
        if (static::isPublic()) {
            return 'public class member';
        }
        if (static::isProtected()) {
            return 'protected class member';
        }
        if (static::isPrivate()) {
            return 'private class member';
        }

        return __METHOD__.' failed';
    }
}
