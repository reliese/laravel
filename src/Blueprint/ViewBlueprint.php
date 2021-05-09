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

    /**
     * @inheritDoc
     */
    public function getUniqueName(): string
    {
        return sprintf('%s.%s',
            $this->getSchemaBlueprint()->getSchemaName(),
            $this->getName());
    }
}
