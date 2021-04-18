<?php

namespace Reliese\Blueprint;

use Doctrine\DBAL\Schema\View;
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
     * @param string $name
     */
    public function __construct(SchemaBlueprint $schemaBlueprint, string $name)
    {
        $this->setSchemaBlueprint($schemaBlueprint);
        $this->setName($name);
    }

    /**
     * @inheritDoc
     */
    public function getSchemaMemberType(): SchemaMemberType
    {
        return SchemaMemberType::View();
    }
}
