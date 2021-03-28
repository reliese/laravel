<?php

namespace Reliese\Blueprint;

/**
 * Class TableBlueprint
 */
class TableBlueprint implements SchemaMemberInterface, ColumnOwnerInterface
{
    use SchemaMemberTrait;
    use ColumnOwnerTrait;

    /**
     * TableBlueprint constructor.
     *
     * @param SchemaBlueprint $schemaBlueprint
     * @param string          $tableName
     */
    public function __construct(SchemaBlueprint $schemaBlueprint,
        string $tableName)
    {
        $this->setSchemaBlueprint($schemaBlueprint);
        $this->setName($tableName);
    }

    /**
     * @inheritDoc
     */
    public function getSchemaMemberType(): SchemaMemberType
    {
        return SchemaMemberType::Table();
    }
}