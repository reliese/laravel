<?php

/**
 * Created by Cristian.
 * Date: 11/09/16 12:11 PM.
 */

namespace Reliese\Coders\Model;

use Illuminate\Support\Str;
use Reliese\Meta\Blueprint;
use Illuminate\Support\Fluent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Reliese\Coders\Model\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Reliese\Coders\Model\Relations\ReferenceFactory;

class Model
{
    /**
     * @var \Reliese\Meta\Blueprint
     */
    private $blueprint;

    /**
     * @var \Reliese\Coders\Model\Factory
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
     * @var \Reliese\Meta\Blueprint[]
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
     * @var \Reliese\Coders\Model\Mutator[]
     */
    protected $mutators = [];

    /**
     * @var \Reliese\Coders\Model\Mutation[]
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
     * @var \Illuminate\Support\Fluent
     */
    protected $primaryKeys;

    /**
     * @var \Illuminate\Support\Fluent
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
     * ModelClass constructor.
     *
     * @param \Reliese\Meta\Blueprint $blueprint
     * @param \Reliese\Coders\Model\Factory $factory
     * @param \Reliese\Coders\Model\Mutator[] $mutators
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

    protected function configure()
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
     * @param \Illuminate\Support\Fluent $column
     */
    protected function parseColumn(Fluent $column)
    {
        // TODO: Check type cast is OK
        $cast = $column->type;

        // Due to some casting problems when converting null to a Carbon instance,
        // we are going to treat Soft Deletes field as string.
        if ($column->name == $this->getDeletedAtField()) {
            $cast = 'string';
        }

        // Track dates
        if ($cast == 'date') {
            $this->dates[] = $column->name;
        }
        // Track attribute casts
        elseif ($cast != 'string') {
            $this->casts[$column->name] = $cast;
        }

        foreach ($this->config('casts', []) as $pattern => $casting) {
            if (Str::is($pattern, $column->name)) {
                $this->casts[$column->name] = $cast = $casting;
                break;
            }
        }

        if ($this->isHidden($column->name)) {
            $this->hidden[] = $column->name;
        }

        if ($this->isFillable($column->name)) {
            $this->fillable[] = $column->name;
        }

        $this->mutate($column->name);

        // Track comment hints
        if (! empty($column->comment)) {
            $this->hints[$column->name] = $column->comment;
        }

        // Track PHP type hints
        $hint = $this->phpTypeHint($cast);
        $this->properties[$column->name] = $hint;

        if ($column->name == $this->getPrimaryKey()) {
            $this->primaryKeyColumn = $column;
        }
    }

    /**
     * @param string $column
     */
    protected function mutate($column)
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
     * @param \Illuminate\Support\Fluent $relation
     *
     * @return $this|\Reliese\Coders\Model\Model
     */
    public function makeRelationModel(Fluent $relation)
    {
        list($database, $table) = array_values($relation->on);

        if ($this->blueprint->is($database, $table)) {
            return $this;
        }

        return $this->factory->makeModel($database, $table, false);
    }

    /**
     * @param string $castType
     *
     * @todo Make tests
     *
     * @return string
     */
    public function phpTypeHint($castType)
    {
        switch ($castType) {
            case 'object':
                return '\stdClass';
            case 'array':
            case 'json':
                return 'array';
            case 'collection':
                return '\Illuminate\Support\Collection';
            case 'date':
                return '\Carbon\Carbon';
            case 'binary':
                return 'string';
            default:
                return $castType;
        }
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return $this->blueprint->schema();
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->blueprint->table();
    }

    /**
     * @return string
     */
    public function getQualifiedTable()
    {
        return $this->blueprint->qualifiedTable();
    }

    /**
     * @return string
     */
    public function getTableForQuery()
    {
        return $this->shouldQualifyTableName()
            ? $this->getQualifiedTable()
            : $this->getTable();
    }

    /**
     * @return bool
     */
    public function shouldQualifyTableName()
    {
        return $this->config('qualified_tables', false);
    }

    /**
     * @param \Reliese\Meta\Blueprint[] $references
     */
    public function withReferences($references)
    {
        $this->references = $references;
    }

    /**
     * @param string $namespace
     *
     * @return $this
     */
    public function withNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getBaseNamespace()
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
    public function withParentClass($parent)
    {
        $this->parentClass = $parent;

        return $this;
    }

    /**
     * @return string
     */
    public function getParentClass()
    {
        return $this->parentClass;
    }

    /**
     * @return string
     */
    public function getQualifiedUserClassName()
    {
        return '\\'.$this->getNamespace().'\\'.$this->getClassName();
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return Str::studly($this->getRecordName());
    }

    /**
     * @return string
     */
    public function getRecordName()
    {
        return Str::singular($this->blueprint->table());
    }

    /**
     * @param bool $timestampsEnabled
     *
     * @return $this
     */
    public function withTimestamps($timestampsEnabled)
    {
        $this->timestamps = $timestampsEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function usesTimestamps()
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
    public function withCreatedAtField($field)
    {
        $this->CREATED_AT = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAtField()
    {
        return $this->CREATED_AT;
    }

    /**
     * @return bool
     */
    public function hasCustomCreatedAtField()
    {
        return $this->usesTimestamps() &&
               $this->getCreatedAtField() != $this->getDefaultCreatedAtField();
    }

    /**
     * @return string
     */
    public function getDefaultCreatedAtField()
    {
        return Eloquent::CREATED_AT;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function withUpdatedAtField($field)
    {
        $this->UPDATED_AT = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAtField()
    {
        return $this->UPDATED_AT;
    }

    /**
     * @return bool
     */
    public function hasCustomUpdatedAtField()
    {
        return $this->usesTimestamps() &&
               $this->getUpdatedAtField() != $this->getDefaultUpdatedAtField();
    }

    /**
     * @return string
     */
    public function getDefaultUpdatedAtField()
    {
        return Eloquent::UPDATED_AT;
    }

    /**
     * @param bool $softDeletesEnabled
     *
     * @return $this
     */
    public function withSoftDeletes($softDeletesEnabled)
    {
        $this->softDeletes = $softDeletesEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function usesSoftDeletes()
    {
        return $this->softDeletes &&
               $this->blueprint->hasColumn($this->getDeletedAtField());
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function withDeletedAtField($field)
    {
        $this->DELETED_AT = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeletedAtField()
    {
        return $this->DELETED_AT;
    }

    /**
     * @return bool
     */
    public function hasCustomDeletedAtField()
    {
        return $this->usesSoftDeletes() &&
               $this->getDeletedAtField() != $this->getDefaultDeletedAtField();
    }

    /**
     * @return string
     */
    public function getDefaultDeletedAtField()
    {
        return 'deleted_at';
    }

    /**
     * @return array
     */
    public function getTraits()
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
    public function needsTableName()
    {
        return $this->blueprint->table() != Str::plural($this->getRecordName()) ||
               $this->shouldQualifyTableName();
    }

    /**
     * @param bool $showConnection
     */
    public function withConnection($showConnection)
    {
        $this->showConnection = $showConnection;
    }

    /**
     * @param string $connection
     */
    public function withConnectionName($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return bool
     */
    public function shouldShowConnection()
    {
        return (bool) $this->showConnection;
    }

    /**
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connection;
    }

    /**
     * @return bool
     */
    public function hasCustomPrimaryKey()
    {
        return count($this->primaryKeys->columns) == 1 &&
               $this->getPrimaryKey() != $this->getDefaultPrimaryKeyField();
    }

    /**
     * @return string
     */
    public function getDefaultPrimaryKeyField()
    {
        return 'id';
    }

    /**
     * @todo: Improve it
     * @return string
     */
    public function getPrimaryKey()
    {
        if (empty($this->primaryKeys->columns)) {
            return;
        }

        return $this->primaryKeys->columns[0];
    }

    /**
     * @return string
     * @todo: check
     */
    public function getPrimaryKeyType()
    {
        return $this->primaryKeyColumn->type;
    }

    /**
     * @todo: Check whether it is necessary
     * @return bool
     */
    public function hasCustomPrimaryKeyCast()
    {
        return $this->getPrimaryKeyType() != $this->getDefaultPrimaryKeyType();
    }

    /**
     * @return string
     */
    public function getDefaultPrimaryKeyType()
    {
        return 'int';
    }

    /**
     * @return bool
     */
    public function doesNotAutoincrement()
    {
        return ! $this->autoincrement();
    }

    /**
     * @return bool
     */
    public function autoincrement()
    {
        if ($this->primaryKeyColumn) {
            return $this->primaryKeyColumn->autoincrement === true;
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
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return bool
     */
    public function hasCustomPerPage()
    {
        return $this->perPage != $this->getDefaultPerPage();
    }

    /**
     * @return int
     */
    public function getDefaultPerPage()
    {
        return 15;
    }

    /**
     * @param string $format
     *
     * @return $this
     */
    public function withDateFormat($format)
    {
        $this->dateFormat = $format;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * @return bool
     */
    public function hasCustomDateFormat()
    {
        return $this->dateFormat != $this->getDefaultDateFormat();
    }

    /**
     * @return string
     */
    public function getDefaultDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * @return bool
     */
    public function hasCasts()
    {
        return ! empty($this->getCasts());
    }

    /**
     * @return array
     */
    public function getCasts()
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
    public function hasDates()
    {
        return ! empty($this->getDates());
    }

    /**
     * @return array
     */
    public function getDates()
    {
        return array_diff($this->dates, [$this->CREATED_AT, $this->UPDATED_AT]);
    }

    /**
     * @return bool
     */
    public function usesSnakeAttributes()
    {
        return (bool) $this->config('snake_attributes', true);
    }

    /**
     * @return bool
     */
    public function doesNotUseSnakeAttributes()
    {
        return ! $this->usesSnakeAttributes();
    }

    /**
     * @return bool
     */
    public function hasHints()
    {
        return ! empty($this->getHints());
    }

    /**
     * @return array
     */
    public function getHints()
    {
        return $this->hints;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasProperty($name)
    {
        return array_key_exists($name, $this->getProperties());
    }

    /**
     * @return \Reliese\Coders\Model\Relation[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @return bool
     */
    public function hasRelations()
    {
        return ! empty($this->relations);
    }

    /**
     * @return \Reliese\Coders\Model\Mutation[]
     */
    public function getMutations()
    {
        return $this->mutations;
    }

    /**
     * @param string $column
     *
     * @return bool
     */
    public function isHidden($column)
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
    public function hasHidden()
    {
        return ! empty($this->hidden);
    }

    /**
     * @return array
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param string $column
     *
     * @return bool
     */
    public function isFillable($column)
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

        if ($this->primaryKeys->columns) {
            $protected = array_merge($protected, $this->primaryKeys->columns);
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
    public function hasFillable()
    {
        return ! empty($this->fillable);
    }

    /**
     * @return array
     */
    public function getFillable()
    {
        return $this->fillable;
    }

    /**
     * @return \Reliese\Meta\Blueprint
     */
    public function getBlueprint()
    {
        return $this->blueprint;
    }

    /**
     * @param \Illuminate\Support\Fluent $command
     *
     * @return bool
     */
    public function isPrimaryKey(Fluent $command)
    {
        foreach ((array) $this->primaryKeys->columns as $column) {
            if (! in_array($column, $command->columns)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \Illuminate\Support\Fluent $command
     *
     * @return bool
     */
    public function isUniqueKey(Fluent $command)
    {
        return $this->blueprint->isUniqueKey($command);
    }

    /**
     * @return bool
     */
    public function usesBaseFiles()
    {
        return $this->config('base_files', false);
    }

    /**
     * @return bool
     */
    public function usesHints()
    {
        return $this->config('hints', false);
    }

    /**
     * @return bool
     */
    public function doesNotUseBaseFiles()
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
}
