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
     * @param string $foreignKeyName
     *
     * @return ForeignKeyBlueprint
     */
    public function getForeignKeyBlueprint(string $foreignKeyName) : ForeignKeyBlueprint
    {
        return $this->foreignKeyBlueprints[$foreignKeyName];
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
     * @return ForeignKeyBlueprint[]
     */
    public function getForeignKeyBlueprints() : array
    {
        return $this->foreignKeyBlueprints;
    }

    /**
     * @return array
     */
    public function getForeignKeyBlueprintsGroupedByReferencedTable() : array
    {
        $results = [];
        foreach ($this->foreignKeyBlueprints as $foreignKeyName => $foreignKeyBlueprint) {
            $results[$foreignKeyBlueprint->getReferencedTableName()][$foreignKeyName] = $foreignKeyBlueprint;
        }
        return $results;
    }

    /**
     * @param bool $includePrimaryKey
     *
     * @return array[]
     */
    public function getUniqueColumnGroups(bool $includePrimaryKey = true): array
    {
        $uniqueColumnGroups = [];

        foreach ($this->indexBlueprints as $indexBlueprint) {
            if ($indexBlueprint->isPrimaryKey() && !$includePrimaryKey) {
                continue;
            }
            if (!$indexBlueprint->isUnique()) {
                continue;
            }

            $uniqueColumnGroups[] = $indexBlueprint->getColumnBlueprints();
        }

        return $uniqueColumnGroups;
    }

    /**
     * @param bool $includePrimaryKey
     *
     * @return IndexBlueprint[]
     */
    public function getUniqueIndexes(bool $includePrimaryKey = true): array
    {
        $uniqueIndexes = [];

        foreach ($this->indexBlueprints as $indexBlueprint) {
            if ($indexBlueprint->isPrimaryKey() && !$includePrimaryKey) {
                continue;
            }
            if (!$indexBlueprint->isUnique()) {
                continue;
            }

            $uniqueIndexes[] = $indexBlueprint;
        }

        usort($uniqueIndexes, function (IndexBlueprint $a, IndexBlueprint $b) {
            return strncasecmp($a->getName(),
            $b->getName(), mb_strlen($a->getName()));});

        return $uniqueIndexes;
    }
}
