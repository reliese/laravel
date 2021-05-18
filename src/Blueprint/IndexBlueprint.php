<?php

namespace Reliese\Blueprint;

/**
 * Class IndexBlueprint
 */
class IndexBlueprint implements ColumnOwnerInterface
{
    use ColumnOwnerTrait;
    use SchemaMemberTrait;

    /**
     * @var string
     */
    private string $indexName;

    /**
     * @var bool
     */
    private bool $isPrimaryKey;

    /**
     * @var bool
     */
    private bool $isUnique;

    /**
     * @var ColumnOwnerInterface
     */
    private ColumnOwnerInterface $owner;

    /**
     * IndexBlueprint constructor.
     *
     * @param ColumnOwnerInterface $tableOrView
     * @param string $indexName
     * @param ColumnBlueprint[] $columnBlueprints
     * @param bool $isPrimary
     * @param bool $isUnique
     */
    public function __construct(ColumnOwnerInterface $tableOrView,
        string $indexName,
        array $columnBlueprints,
        bool $isPrimary,
        bool $isUnique)
    {
        $this->owner = $tableOrView;
        $this->indexName = $indexName;
        $this->addColumnBlueprints($columnBlueprints);
        $this->isPrimaryKey = $isPrimary;
        $this->isUnique = $isUnique;

        /** @var ColumnBlueprint $columnBlueprint */
        foreach ($columnBlueprints as $columnBlueprint) {
            $columnBlueprint->addIndexReference($this);
        }
    }

    /**
     * @return string
     */
    public function getUniqueName(): string
    {
        return $this->indexName;
    }

    /**
     * @return bool
     */
    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    /**
     * @return bool
     */
    public function isUnique(): bool
    {
        return $this->isUnique || $this->isPrimaryKey();
    }

    public function getSchemaMemberType(): SchemaMemberType
    {
        return SchemaMemberType::Index();
    }
}
