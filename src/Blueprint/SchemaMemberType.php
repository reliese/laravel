<?php

namespace Reliese\Blueprint;

/**
 * Class SchemaMemberType
 */
class SchemaMemberType
{
    protected const FOREIGN_KEY = 'FOREIGN_KEY';
    protected const INDEX = 'INDEX';
    protected const TABLE = 'TABLE';
    protected const VIEW = 'VIEW';

    /**
     * @var SchemaMemberType|null
     */
    private static $sharedView;

    /**
     * @var string
     */
    private $typeName;

    /**
     * SchemaMemberType constructor.
     *
     * @param string $typeName
     */
    protected function __construct(string $typeName)
    {
        $this->typeName = $typeName;
    }

    /**
     * @return SchemaMemberType
     */
    public static function ForeignKey() : SchemaMemberType
    {
        if (empty(static::$sharedView)) {
            static::$sharedView = new SchemaMemberType(static::FOREIGN_KEY);
        }

        return static::$sharedView;
    }

    /**
     * @return SchemaMemberType
     */
    public static function Index() : SchemaMemberType
    {
        if (empty(static::$sharedView)) {
            static::$sharedView = new SchemaMemberType(static::INDEX);
        }

        return static::$sharedView;
    }

    /**
     * @return SchemaMemberType
     */
    public static function Table() : SchemaMemberType
    {
        if (empty(static::$sharedView)) {
            static::$sharedView = new SchemaMemberType(static::TABLE);
        }

        return static::$sharedView;
    }

    /**
     * @return SchemaMemberType
     */
    public static function View() : SchemaMemberType
    {
        if (empty(static::$sharedView)) {
            static::$sharedView = new SchemaMemberType(static::VIEW);
        }

        return static::$sharedView;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->typeName;
    }
}