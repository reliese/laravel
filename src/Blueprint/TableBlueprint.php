<?php

namespace Reliese\Blueprint;

use Doctrine\DBAL\Schema\Table;
/**
 * Class TableBlueprint
 */
class TableBlueprint implements SchemaMemberInterface, ColumnOwnerInterface
{
    use SchemaMemberTrait;
    use ColumnOwnerTrait;

    /**
     * @var ForeignKeyBlueprint[]
     */
    private array $foreignKeyBlueprints = [];

    /**
     * @var IndexBlueprint[]
     */
    private array $indexBlueprints = [];

    /**
     * @var UniqueKeyBlueprint[]
     */
    private array $uniqueKeyBlueprints = [];

    /**
     * TableBlueprint constructor.
     *
     * @param SchemaBlueprint $schemaBlueprint
     * @param string $tableName
     */
    public function __construct(
        SchemaBlueprint $schemaBlueprint,
        string $tableName
    ) {
        $this->setSchemaBlueprint($schemaBlueprint);
        $this->setName($tableName);
    }

    /**
     * @param IndexBlueprint $indexBlueprint
     *
     * @return $this
     */
    public function addIndexBlueprint(IndexBlueprint $indexBlueprint): static
    {
        $this->indexBlueprints[$indexBlueprint->getName()] = $indexBlueprint;
        return $this;
    }

    /**
     * @param UniqueKeyBlueprint $uniqueKeyBlueprint
     *
     * @return $this
     */
    public function addUniqueConstraintBlueprint(UniqueKeyBlueprint $uniqueKeyBlueprint) : static
    {
        $this->uniqueKeyBlueprints[$uniqueKeyBlueprint->getName()] = $uniqueKeyBlueprint;
        return $this;
    }

    /**
     * @param ForeignKeyBlueprint $foreignKeyBlueprint
     *
     * @return $this
     */
    public function addForeignKeyBlueprint(ForeignKeyBlueprint $foreignKeyBlueprint): static
    {
        $this->foreignKeyBlueprints[$foreignKeyBlueprint->getName()] = $foreignKeyBlueprint;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getForeignKeyNames() : array
    {
        return \array_keys($this->foreignKeyBlueprints);
    }

    /**
     * @inheritDoc
     */
    public function getSchemaMemberType(): SchemaMemberType
    {
        return SchemaMemberType::Table();
    }

    /**
     * @return string
     */
    public function getUniqueName(): string
    {
        return sprintf(
            '%s.%s',
            $this->getSchemaBlueprint()->getSchemaName(),
            $this->getName()
        );
    }
}
