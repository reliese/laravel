<?php

/**
 * Created by Cristian.
 * Date: 11/09/16 12:11 PM.
 */

namespace Reliese\Coders\Model;

use Illuminate\Support\Str;
use Reliese\Meta\Blueprint;
use Illuminate\Database\Eloquent\SoftDeletes;
use Reliese\Coders\Model\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Reliese\Coders\Model\Relations\ReferenceFactory;
use Reliese\Meta\Column;
use Reliese\Meta\Index;

class Model
{
    /**
     * @var Blueprint
     */
    private $blueprint;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var Relation[]
     */
    protected $relations = [];

    /**
     * @var Blueprint[]
     */
    protected $references = [];

    /**
     * @var array
     */
    protected $hidden = [];

    /**
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $casts = [];

    /**
     * @var Mutator[]
     */
    protected $mutators = [];

    /**
     * @var Mutation[]
     */
    protected $mutations = [];

    /**
     * @var array
     */
    protected $dates = [];

    /**
     * @var array
     */
    protected $hints = [];

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $parentClass;

    /**
     * @var bool
     */
    protected $timestamps = true;

    /**
     * @var string
     */
    protected $CREATED_AT;

    /**
     * @var string
     */
    protected $UPDATED_AT;

    /**
     * @var bool
     */
    protected $softDeletes = false;

    /**
     * @var string
     */
    protected $DELETED_AT;

    /**
     * @var bool
     */
    protected $showConnection = false;

    /**
     * @var string
     */
    protected $connection;

    /**
     * @var Index
     */
    protected $primaryKeys;

    /**
     * @var Column
     */
    protected $primaryKeyColumn;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @var string
     */
    protected $dateFormat;

    /**
     * @var bool
     */
    protected $loadRelations;

    /**
     * @var bool
     */
    protected $hasCrossDatabaseRelationships = false;

    /**
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * @var string
     */
    protected $relationNameStrategy = '';

    /**
     * ModelClass constructor.
     *
     * @param Blueprint $blueprint
     * @param Factory $factory
     * @param Mutator[] $mutators
     * @param bool $loadRelations
     */
    public function __construct(Blueprint $blueprint, Factory $factory, $mutators = [], $loadRelations = true)
    {
        $this->blueprint = $blueprint;
        $this->factory = $factory;
        $this->loadRelations = $loadRelations;
        $this->mutators = $mutators;
        $this->configure();
        $this->fill();
    }

    protected function configure(): Model
    {
        $this->withNamespace($this->config('namespace'));
        $this->withParentClass($this->config('parent'));

        // Timestamps settings
        $this->withTimestamps($this->config('timestamps.enabled', $this->config('timestamps', true)));
        $this->withCreatedAtField($this->config('timestamps.fields.CREATED_AT', $this->getDefaultCreatedAtField()));
        $this->withUpdatedAtField($this->config('timestamps.fields.UPDATED_AT', $this->getDefaultUpdatedAtField()));

        // Soft deletes settings
        $this->withSoftDeletes($this->config('soft_deletes.enabled', $this->config('soft_deletes', false)));
        $this->withDeletedAtField($this->config('soft_deletes.field', $this->getDefaultDeletedAtField()));

        // Connection settings
        $this->withConnection($this->config('connection', false));
        $this->withConnectionName($this->blueprint->connection());

        // Pagination settings
        $this->withPerPage($this->config('per_page', $this->getDefaultPerPage()));

        // Dates settings
        $this->withDateFormat($this->config('date_format', $this->getDefaultDateFormat()));

        // Table Prefix settings
        $this->withTablePrefix($this->config('table_prefix', $this->getDefaultTablePrefix()));

        // Relation name settings
        $this->withRelationNameStrategy($this->config('relation_name_strategy', $this->getDefaultRelationNameStrategy()));

        return $this;
    }

    /**
     * Parses the model information.
     */
    protected function fill()
    {
        $this->primaryKeys = $this->blueprint->primaryKey();

        // Process columns
        foreach ($this->blueprint->columns() as $column) {
            $this->parseColumn($column);
        }

        if (! $this->loadRelations) {
            return;
        }

        foreach ($this->blueprint->relations() as $relation) {
            $model = $this->makeRelationModel($relation);
            $belongsTo = new BelongsTo($relation, $this, $model);
            $this->relations[$belongsTo->name()] = $belongsTo;
        }

        foreach ($this->factory->referencing($this) as $related) {
            $factory = new ReferenceFactory($related, $this);
            $references = $factory->make();
            foreach ($references as $reference) {
                $this->relations[$reference->name()] = $reference;
            }
        }
    }

    /**
     * @param Column $column
     */
    protected function parseColumn(Column $column)
    {
        // TODO: Check type cast is OK
        $cast = $column->getType();

        $propertyName = $this->usesPropertyConstants() ? 'self::'.strtoupper($column->getName()) : $column->getName();

        // Due to some casting problems when converting null to a Carbon instance,
        // we are going to treat Soft Deletes field as string.
        if ($column->getName() == $this->getDeletedAtField()) {
            $cast = 'string';
        }

        // Track dates
        if ($cast == 'date') {
            $this->dates[] = $propertyName;
        }
        // Track attribute casts
        elseif ($cast != 'string') {
            $this->casts[$propertyName] = $cast;
        }

        foreach ($this->config('casts', []) as $pattern => $casting) {
            if (Str::is($pattern, $column->getName())) {
                $this->casts[$propertyName] = $cast = $casting;
                break;
            }
        }

        if ($this->isHidden($column->getName())) {
            $this->hidden[] = $propertyName;
        }

        if ($this->isFillable($column->getName())) {
            $this->fillable[] = $propertyName;
        }

        $this->mutate($column->getName());

        // Track comment hints
        if (! empty($column->getComment())) {
            $this->hints[$column->getName()] = $column->getComment();
        }

        // Track PHP type hints
        $hint = $this->phpTypeHint($cast, $column->isNullable());
        $this->properties[$column->getName()] = $hint;

        // TODO: Handle Composite Primary Keys
        if ($column->getName() == $this->getPrimaryKey()) {
            $this->primaryKeyColumn = $column;
        }
    }

    /**
     * @param string $column
     */
    protected function mutate(string $column)
    {
        foreach ($this->mutators as $mutator) {
            if ($mutator->applies($column, $this->getBlueprint())) {
                $this->mutations[] = new Mutation(
                    $mutator->getName($column, $this),
                    $mutator->getBody($column, $this)
                );
            }
        }
    }

    /**
     * @param \Reliese\Meta\Relation $relation
     *
     * @return Model
     */
    public function makeRelationModel(\Reliese\Meta\Relation $relation): Model
    {
        [$database, $table] = array_values($relation->getOnTable());

        if ($this->blueprint->is($database, $table)) {
            return $this;
        }

        return $this->factory->makeModel($database, $table, false);
    }

    /**
     * @param string $castType
     * @param bool $nullable
     *
     * @return string
     * @todo Make tests
     */
    public function phpTypeHint(string $castType, bool $nullable): string
    {
        $type = $castType;

        switch ($castType) {
            case 'object':
                $type = '\stdClass';
                break;
            case 'array':
            case 'json':
                $type = 'array';
                break;
            case 'collection':
                $type = '\Illuminate\Support\Collection';
                break;
            case 'date':
                $type = '\Carbon\Carbon';
                break;
            case 'binary':
                $type = 'string';
                break;
        }

        if ($nullable) {
            return $type.'|null';
        }

        return $type;
    }

    /**
     * @return string
     */
    public function getSchema(): string
    {
        return $this->blueprint->schema();
    }

    /**
     * @param bool $andRemovePrefix
     *
     * @return string
     */
    public function getTable($andRemovePrefix = false): string
    {
        if ($andRemovePrefix) {
            return $this->removeTablePrefix($this->blueprint->table());
        }

        return $this->blueprint->table();
    }

    /**
     * @return string
     */
    public function getQualifiedTable(): string
    {
        return $this->blueprint->qualifiedTable();
    }

    /**
     * @return string
     */
    public function getTableForQuery(): string
    {
        return $this->shouldQualifyTableName()
            ? $this->getQualifiedTable()
            : $this->getTable();
    }

    /**
     * @return bool
     */
    public function shouldQualifyTableName(): bool
    {
        return $this->config('qualified_tables', false);
    }

    /**
     * @return bool
     */
    public function shouldPluralizeTableName(): bool
    {
        $pluralize = (bool) $this->config('pluralize', true);

        $overridePluralizeFor = $this->config('override_pluralize_for', []);
        if (count($overridePluralizeFor) > 0) {
            foreach ($overridePluralizeFor as $except) {
                if ($except == $this->getTable()) {
                    return ! $pluralize;
                }
            }
        }

        return $pluralize;
    }

    /**
     * @return bool
     */
    public function shouldLowerCaseTableName(): bool
    {
        return (bool) $this->config('lower_table_name_first', false);
    }

    /**
     * @param Blueprint[] $references
     */
    public function withReferences(array $references)
    {
        $this->references = $references;
    }

    /**
     * @param string $namespace
     *
     * @return $this
     */
    public function withNamespace(string $namespace): Model
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getRelationNameStrategy(): string
    {
        return $this->relationNameStrategy;
    }

    /**
     * @return string
     */
    public function getBaseNamespace(): string
    {
        return $this->usesBaseFiles()
            ? $this->getNamespace().'\\Base'
            : $this->getNamespace();
    }

    /**
     * @param string $parent
     *
     * @return $this
     */
    public function withParentClass(string $parent): Model
    {
        $this->parentClass = '\\' . ltrim($parent, '\\');

        return $this;
    }

    /**
     * @return string
     */
    public function getParentClass(): string
    {
        return $this->parentClass;
    }

    /**
     * @return string
     */
    public function getQualifiedUserClassName(): string
    {
        return '\\'.$this->getNamespace().'\\'.$this->getClassName();
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        // Model names can be manually overridden by users in the config file.
        // If a config entry exists for this table, use that name, rather than generating one.
        $overriddenName = $this->config('model_names.' . $this->getTable());
        if ($overriddenName) {
            return $overriddenName;
        }

        if ($this->shouldLowerCaseTableName()) {
            return Str::studly(Str::lower($this->getRecordName()));
        }

        return Str::studly($this->getRecordName());
    }

    /**
     * @return string
     */
    public function getRecordName(): string
    {
        if ($this->shouldPluralizeTableName()) {
            return Str::singular($this->removeTablePrefix($this->blueprint->table()));
        }

        return $this->removeTablePrefix($this->blueprint->table());
    }

    /**
     * @param bool $timestampsEnabled
     *
     * @return $this
     */
    public function withTimestamps(bool $timestampsEnabled): Model
    {
        $this->timestamps = $timestampsEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function usesTimestamps(): bool
    {
        return $this->timestamps &&
               $this->blueprint->hasColumn($this->getCreatedAtField()) &&
               $this->blueprint->hasColumn($this->getUpdatedAtField());
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function withCreatedAtField(string $field): Model
    {
        $this->CREATED_AT = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAtField(): string
    {
        return $this->CREATED_AT;
    }

    /**
     * @return bool
     */
    public function hasCustomCreatedAtField(): bool
    {
        return $this->usesTimestamps() &&
               $this->getCreatedAtField() != $this->getDefaultCreatedAtField();
    }

    /**
     * @return string
     */
    public function getDefaultCreatedAtField(): string
    {
        return Eloquent::CREATED_AT;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function withUpdatedAtField(string $field): Model
    {
        $this->UPDATED_AT = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAtField(): string
    {
        return $this->UPDATED_AT;
    }

    /**
     * @return bool
     */
    public function hasCustomUpdatedAtField(): bool
    {
        return $this->usesTimestamps() &&
               $this->getUpdatedAtField() != $this->getDefaultUpdatedAtField();
    }

    /**
     * @return string
     */
    public function getDefaultUpdatedAtField(): string
    {
        return Eloquent::UPDATED_AT;
    }

    /**
     * @param bool $softDeletesEnabled
     *
     * @return $this
     */
    public function withSoftDeletes(bool $softDeletesEnabled): Model
    {
        $this->softDeletes = $softDeletesEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function usesSoftDeletes(): bool
    {
        return $this->softDeletes &&
               $this->blueprint->hasColumn($this->getDeletedAtField());
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function withDeletedAtField(string $field): Model
    {
        $this->DELETED_AT = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeletedAtField(): string
    {
        return $this->DELETED_AT;
    }

    /**
     * @return bool
     */
    public function hasCustomDeletedAtField(): bool
    {
        return $this->usesSoftDeletes() &&
               $this->getDeletedAtField() != $this->getDefaultDeletedAtField();
    }

    /**
     * @return string
     */
    public function getDefaultDeletedAtField(): string
    {
        return 'deleted_at';
    }

    /**
     * @return array
     */
    public function getTraits(): array
    {
        $traits = $this->config('use', []);

        if (! is_array($traits)) {
            throw new \RuntimeException('Config use must be an array of valid traits to append to each model.');
        }

        if ($this->usesSoftDeletes()) {
            $traits = array_merge([SoftDeletes::class], $traits);
        }

        return $traits;
    }

    /**
     * @return bool
     */
    public function needsTableName(): bool
    {
        return false === $this->shouldQualifyTableName() ||
            $this->shouldRemoveTablePrefix() ||
            $this->blueprint->table() != Str::plural($this->getRecordName()) ||
            ! $this->shouldPluralizeTableName();
    }

    /**
     * @return string
     */
    public function shouldRemoveTablePrefix()
    {
        return ! empty($this->tablePrefix);
    }

    /**
     * @param string $tablePrefix
     */
    public function withTablePrefix(string $tablePrefix)
    {
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * @param string $relationNameStrategy
     */
    public function withRelationNameStrategy(string $relationNameStrategy)
    {
        $this->relationNameStrategy = $relationNameStrategy;
    }

    /**
     * @param string $table
     *
     * @return string
     */
    public function removeTablePrefix(string $table): string
    {
        if (($this->shouldRemoveTablePrefix()) && (substr($table, 0, strlen($this->tablePrefix)) == $this->tablePrefix)) {
            $table = substr($table, strlen($this->tablePrefix));

            if ($table === false) {
                throw new \RuntimeException('Table name must not be empty after removing prefix. Check your configuration and adjust the table prefix.');
            }
        }

        return $table;
    }

    /**
     * @param bool $showConnection
     */
    public function withConnection(bool $showConnection)
    {
        $this->showConnection = $showConnection;
    }

    /**
     * @param string $connection
     */
    public function withConnectionName(string $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return bool
     */
    public function shouldShowConnection(): bool
    {
        return (bool) $this->showConnection;
    }

    /**
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->connection;
    }

    /**
     * @return bool
     */
    public function hasCustomPrimaryKey(): bool
    {
        return count($this->primaryKeys->getColumns()) == 1 &&
               $this->getPrimaryKey() != $this->getDefaultPrimaryKeyField();
    }

    /**
     * @return string
     */
    public function getDefaultPrimaryKeyField(): string
    {
        return 'id';
    }

    /**
     * @todo: Improve it
     * @return string|null
     */
    public function getPrimaryKey(): ?string
    {
        if (empty($this->primaryKeys->getColumns())) {
            return null;
        }

        return $this->primaryKeys->getColumns()[0];
    }

    /**
     * @return string
     * @todo: check
     */
    public function getPrimaryKeyType(): string
    {
        return $this->primaryKeyColumn->getType();
    }

    /**
     * @todo: Check whether it is necessary
     * @return bool
     */
    public function hasCustomPrimaryKeyCast(): bool
    {
        return $this->getPrimaryKeyType() != $this->getDefaultPrimaryKeyType();
    }

    /**
     * @return string
     */
    public function getDefaultPrimaryKeyType(): string
    {
        return 'int';
    }

    /**
     * @return bool
     */
    public function doesNotAutoincrement(): bool
    {
        return ! $this->autoincrement();
    }

    /**
     * @return bool
     */
    public function autoincrement(): bool
    {
        if ($this->primaryKeyColumn) {
            return $this->primaryKeyColumn->isAutoincrement();
        }

        return false;
    }

    /**
     * @param $perPage
     */
    public function withPerPage($perPage)
    {
        $this->perPage = (int) $perPage;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @return bool
     */
    public function hasCustomPerPage(): bool
    {
        return $this->perPage != $this->getDefaultPerPage();
    }

    /**
     * @return int
     */
    public function getDefaultPerPage(): int
    {
        return 15;
    }

    /**
     * @param string $format
     *
     * @return $this
     */
    public function withDateFormat(string $format): Model
    {
        $this->dateFormat = $format;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    /**
     * @return bool
     */
    public function hasCustomDateFormat(): bool
    {
        return $this->dateFormat != $this->getDefaultDateFormat();
    }

    /**
     * @return string
     */
    public function getDefaultDateFormat(): string
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * @return string
     */
    public function getDefaultTablePrefix(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getDefaultRelationNameStrategy(): string
    {
        return 'related';
    }

    /**
     * @return bool
     */
    public function hasCasts(): bool
    {
        return ! empty($this->getCasts());
    }

    /**
     * @return array
     */
    public function getCasts(): array
    {
        if (
            array_key_exists($this->getPrimaryKey(), $this->casts) &&
            $this->autoincrement()
        ) {
            unset($this->casts[$this->getPrimaryKey()]);
        }

        return $this->casts;
    }

    /**
     * @return bool
     */
    public function hasDates(): bool
    {
        return ! empty($this->getDates());
    }

    /**
     * @return array
     */
    public function getDates(): array
    {
        return array_diff($this->dates, [$this->CREATED_AT, $this->UPDATED_AT]);
    }

    /**
     * @return bool
     */
    public function usesSnakeAttributes(): bool
    {
        return (bool) $this->config('snake_attributes', true);
    }

    /**
     * @return bool
     */
    public function doesNotUseSnakeAttributes(): bool
    {
        return ! $this->usesSnakeAttributes();
    }

    /**
     * @return bool
     */
    public function hasHints(): bool
    {
        return ! empty($this->getHints());
    }

    /**
     * @return array
     */
    public function getHints(): array
    {
        return $this->hints;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasProperty(string $name): bool
    {
        return array_key_exists($name, $this->getProperties());
    }

    /**
     * @return \Reliese\Coders\Model\Relation[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @return bool
     */
    public function hasRelations(): bool
    {
        return ! empty($this->relations);
    }

    /**
     * @return Mutation[]
     */
    public function getMutations(): array
    {
        return $this->mutations;
    }

    /**
     * @param string $column
     *
     * @return bool
     */
    public function isHidden(string $column): bool
    {
        $attributes = $this->config('hidden', []);

        if (! is_array($attributes)) {
            throw new \RuntimeException('Config field [hidden] must be an array of attributes to hide from array or json.');
        }

        foreach ($attributes as $pattern) {
            if (Str::is($pattern, $column)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasHidden(): bool
    {
        return ! empty($this->hidden);
    }

    /**
     * @return array
     */
    public function getHidden(): array
    {
        return $this->hidden;
    }

    /**
     * @param string $column
     *
     * @return bool
     */
    public function isFillable(string $column): bool
    {
        $guarded = $this->config('guarded', []);

        if (! is_array($guarded)) {
            throw new \RuntimeException('Config field [guarded] must be an array of attributes to protect from mass assignment.');
        }

        $protected = [
            $this->getCreatedAtField(),
            $this->getUpdatedAtField(),
            $this->getDeletedAtField(),
        ];

        if ($this->primaryKeys->getColumns()) {
            $protected = array_merge($protected, $this->primaryKeys->getColumns());
        }

        foreach (array_merge($guarded, $protected) as $pattern) {
            if (Str::is($pattern, $column)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function hasFillable(): bool
    {
        return ! empty($this->fillable);
    }

    /**
     * @return array
     */
    public function getFillable(): array
    {
        return $this->fillable;
    }

    /**
     * @return Blueprint
     */
    public function getBlueprint(): Blueprint
    {
        return $this->blueprint;
    }

    /**
     * @param \Reliese\Meta\Relation $command
     *
     * @return bool
     */
    public function isPrimaryKey(\Reliese\Meta\Relation $command): bool
    {
        foreach ($this->primaryKeys->getColumns() as $column) {
            if (! in_array($column, $command->getColumns())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \Reliese\Meta\Relation $command
     *
     * @return bool
     */
    public function isUniqueKey(\Reliese\Meta\Relation $command): bool
    {
        return $this->blueprint->isUniqueKey($command);
    }

    /**
     * @return bool
     */
    public function usesBaseFiles(): bool
    {
        return $this->config('base_files', false);
    }

    /**
     * @return bool
     */
    public function usesPropertyConstants(): bool
    {
        return $this->config('with_property_constants', false);
    }

    /**
     * @return int
     */
    public function indentWithSpace(): int
    {
        return (int) $this->config('indent_with_space', 0);
    }

    /**
     * @return bool
     */
    public function usesHints(): bool
    {
        return $this->config('hints', false);
    }

    /**
     * @return bool
     */
    public function doesNotUseBaseFiles(): bool
    {
        return ! $this->usesBaseFiles();
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function config($key = null, $default = null)
    {
        return $this->factory->config($this->getBlueprint(), $key, $default);
    }

    /**
     * @return bool
     */
    public function fillableInBaseFiles(): bool
    {
        return $this->config('fillable_in_base_files', false);
    }
}
