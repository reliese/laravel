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
     * @var ForeignKeyBlueprint[]
     */
    private array $foreignKeyBlueprints = [];

    /**
     * @var IndexBlueprint[]
     */
    private array $indexBlueprints = [];

    /**
     * @var IndexBlueprint[]
     */
    private array $uniqueKeyBlueprints = [];

    /**
     * TableBlueprint constructor.
     *
     * @param SchemaBlueprint $schemaBlueprint
     * @param string $tableName
     */
    public function __construct(SchemaBlueprint $schemaBlueprint,
        string $tableName)
    {
        $this->setSchemaBlueprint($schemaBlueprint);
        $this->setName($tableName);
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
     * @param IndexBlueprint $indexBlueprint
     *
     * @return $this
     */
    public function addIndexBlueprint(IndexBlueprint $indexBlueprint): static
    {
        $this->indexBlueprints[$indexBlueprint->getUniqueName()] = $indexBlueprint;
        return $this;
    }

    /**
     * @param IndexBlueprint $uniqueIndexBlueprint
     *
     * @return $this
     */
    public function addUniqueConstraintBlueprint(IndexBlueprint $uniqueIndexBlueprint): static
    {
        $this->uniqueKeyBlueprints[$uniqueIndexBlueprint->getUniqueName()] = $uniqueIndexBlueprint;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getForeignKeyNames(): array
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
        return sprintf('%s.%s',
            $this->getSchemaBlueprint()->getSchemaName(),
            $this->getName());
    }
}
