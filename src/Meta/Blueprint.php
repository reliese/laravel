<?php

/**
 * Created by Cristian.
 * Date: 18/09/16 08:19 PM.
 */

namespace Reliese\Meta;

class Blueprint
{
    /**
     * @var string
     */
    protected $connection;

    /**
     * @var string
     */
    protected $schema;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var Column[]
     */
    protected $columns = [];

    /**
     * @var Index[]
     */
    protected $indexes = [];

    /**
     * @var Index[]
     */
    protected $unique = [];

    /**
     * @var Relation[]
     */
    protected $relations = [];

    /**
     * @var Index
     */
    protected $primaryKey;

    /**
     * @var bool
     */
    protected $isView;

    /**
     * Blueprint constructor.
     *
     * @param string $connection
     * @param string $schema
     * @param string $table
     * @param bool   $isView
     */
    public function __construct(string $connection, string $schema, string $table, bool $isView = false)
    {
        $this->connection = $connection;
        $this->schema = $schema;
        $this->table = $table;
        $this->isView = $isView;
    }

    /**
     * @return string
     */
    public function schema(): string
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function table(): string
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function qualifiedTable(): string
    {
        return $this->schema().'.'.$this->table();
    }

    /**
     * @param Column $column
     *
     * @return $this
     */
    public function withColumn(Column $column): Blueprint
    {
        $this->columns[$column->getName()] = $column;

        return $this;
    }

    /**
     * @return Column[]
     */
    public function columns(): array
    {
        return $this->columns;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasColumn(string $name): bool
    {
        return array_key_exists($name, $this->columns);
    }

    /**
     * @param string $name
     *
     * @return Column
     */
    public function column(string $name): Column
    {
        if (! $this->hasColumn($name)) {
            throw new \InvalidArgumentException("Column [$name] does not belong to table [{$this->qualifiedTable()}]");
        }

        return $this->columns[$name];
    }

    /**
     * @param Index $index
     *
     * @return $this
     */
    public function withIndex(Index $index): Blueprint
    {
        $this->indexes[] = $index;

        // TODO: Use constants or proper objects instead of the hardcoded string
        if ($index->getName() == Index::NAME_UNIQUE) {
            $this->unique[] = $index;
        }

        return $this;
    }

    /**
     * @return Index[]
     */
    public function indexes(): array
    {
        return $this->indexes;
    }

    /**
     * @param Relation $index
     *
     * @return $this
     */
    public function withRelation(Relation $index): Blueprint
    {
        $this->relations[] = $index;

        return $this;
    }

    /**
     * @return Relation[]
     */
    public function relations(): array
    {
        return $this->relations;
    }

    /**
     * @param Index $primaryKey
     *
     * @return $this
     */
    public function withPrimaryKey(Index $primaryKey): Blueprint
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * @return Index
     */
    public function primaryKey(): Index
    {
        if ($this->primaryKey) {
            return $this->primaryKey;
        }

        if (! empty($this->unique)) {
            return current($this->unique);
        }

        // Null Primary Key. TODO: Check why we need this and how to make it clear
        return new Index(Index::NAME_PRIMARY, '', []);
    }

    /**
     * @return bool
     */
    public function hasCompositePrimaryKey(): bool
    {
        return count($this->primaryKey->getColumns()) > 1;
    }

    /**
     * @return string
     */
    public function connection(): string
    {
        return $this->connection;
    }

    /**
     * @param string $database
     * @param string $table
     *
     * @return bool
     */
    public function is(string $database, string $table): bool
    {
        return $database == $this->schema() && $table == $this->table();
    }

    /**
     * @param Blueprint $table
     *
     * @return Relation[]
     */
    public function references(Blueprint $table): array
    {
        $references = [];

        foreach ($this->relations() as $relation) {
            [$foreignDatabase, $foreignTable] = array_values($relation->getOnTable());
            if ($table->is($foreignDatabase, $foreignTable)) {
                $references[] = $relation;
            }
        }

        return $references;
    }

    /**
     * @param HasColumns $constraint
     *
     * @return bool
     */
    public function isUniqueKey(HasColumns $constraint): bool
    {
        foreach ($this->unique as $index) {

            // We only need to consider cases, when UNIQUE KEY is presented by only ONE column
            if (count($index->getColumns()) === 1 && isset($index->getColumns()[0])) {
                if (in_array($index->getColumns()[0], $constraint->getColumns())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isView(): bool
    {
        return $this->isView;
    }
}
