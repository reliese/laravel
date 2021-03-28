<?php

namespace Reliese\Blueprint;

/**
 * Class ViewBlueprint
 */
class ViewBlueprint implements SchemaMemberInterface, ColumnOwnerInterface
{
    use SchemaMemberTrait;
    use ColumnOwnerTrait;

    /**
     * @var SchemaBlueprint
     */
    private $schemaBlueprint;

    /**
     * ViewBlueprint constructor.
     *
     * @param SchemaBlueprint $schemaBlueprint
     * @param string          $viewName
     */
    public function __construct(SchemaBlueprint $schemaBlueprint, string $viewName)
    {
        $this->setSchemaBlueprint($schemaBlueprint);
        $this->setName($viewName);
    }

    /**
     * @inheritDoc
     */
    public function getSchemaMemberType(): SchemaMemberType
    {
        return SchemaMemberType::View();
    }
}