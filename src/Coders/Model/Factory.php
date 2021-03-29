<?php

/**
 * Created by Cristian.
 * Date: 19/09/16 11:58 PM.
 */

namespace Reliese\Coders\Model;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Reliese\Meta\Blueprint;
use Reliese\Meta\RelationBag;
use Reliese\Meta\Schema;
use Reliese\Support\Classify;
use Reliese\Meta\SchemaManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\DatabaseManager;

class Factory
{
    /**
     * @var DatabaseManager
     */
    private $db;

    /**
     * @var SchemaManager|Schema[]
     */
    protected $schemas = [];

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Classify
     */
    protected $class;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ModelManager
     */
    protected $models;

    /**
     * @var Mutator[]
     */
    protected $mutators = [];

    /**
     * ModelsFactory constructor.
     *
     * @param DatabaseManager $db
     * @param Filesystem $files
     * @param Classify $writer
     * @param Config $config
     */
    public function __construct(DatabaseManager $db, Filesystem $files, Classify $writer, Config $config)
    {
        $this->db = $db;
        $this->files = $files;
        $this->config = $config;
        $this->class = $writer;
    }

    /**
     * @return Mutator
     */
    public function mutate(): Mutator
    {
        return $this->mutators[] = new Mutator();
    }

    /**
     * @return ModelManager
     */
    protected function models(): ModelManager
    {
        if (! isset($this->models)) {
            $this->models = new ModelManager($this);
        }

        return $this->models;
    }

    /**
     * Select connection to work with.
     *
     * @param string $connection
     *
     * @return Factory
     */
    public function on($connection = null): Factory
    {
        $this->schemas = new SchemaManager($this->db->connection($connection));

        return $this;
    }

    /**
     * @param string $schema
     *
     * @throws FileNotFoundException
     */
    public function map(string $schema)
    {
        if (! isset($this->schemas)) {
            $this->on();
        }

        $mapper = $this->makeSchema($schema);

        foreach ($mapper->tables() as $blueprint) {
            if ($this->shouldTakeOnly($blueprint) && $this->shouldNotExclude($blueprint)) {
                $this->create($mapper->schema(), $blueprint->table());
            }
        }
    }

    /**
     * @param Blueprint $blueprint
     *
     * @return bool
     */
    protected function shouldNotExclude(Blueprint $blueprint): bool
    {
        foreach ($this->config($blueprint, 'except', []) as $pattern) {
            if (Str::is($pattern, $blueprint->table())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Blueprint $blueprint
     *
     * @return bool
     */
    protected function shouldTakeOnly(Blueprint $blueprint): bool
    {
        if ($patterns = $this->config($blueprint, 'only', [])) {
            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $blueprint->table())) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * @param string $schema
     * @param string $table
     *
     * @throws FileNotFoundException
     */
    public function create(string $schema, string $table)
    {
        $model = $this->makeModel($schema, $table);
        $template = $this->prepareTemplate($model, 'model');

        $file = $this->fillTemplate($template, $model);

        if ($model->indentWithSpace()) {
            $file = str_replace("\t", str_repeat(' ', $model->indentWithSpace()), $file);
        }

        $this->files->put($this->modelPath($model, $model->usesBaseFiles() ? ['Base'] : []), $file);

        if ($this->needsUserFile($model)) {
            $this->createUserFile($model);
        }
    }

    /**
     * @param string $schema
     * @param string $table
     * @param bool $withRelations
     *
     * @return Model
     */
    public function makeModel(string $schema, string $table, $withRelations = true): Model
    {
        return $this->models()->make($schema, $table, $this->mutators, $withRelations);
    }

    /**
     * @param string $schema
     *
     * @return Schema
     */
    public function makeSchema(string $schema): Schema
    {
        return $this->schemas->make($schema);
    }

    /**
     * @param Model $model
     *
     * @return RelationBag[]
     *@todo: Delegate workload to SchemaManager and ModelManager
     */
    public function referencing(Model $model): array
    {
        /** @var RelationBag[] $references */
        $references = [];

        // TODO: SchemaManager should do this
        foreach ($this->schemas as $schema) {
            $references = array_merge($references, $schema->referencing($model->getBlueprint()));
        }

        // TODO: ModelManager should do this
        foreach ($references as $related) {
            $blueprint = $related->getBlueprint();

            $model = $model->getBlueprint()->is($blueprint->schema(), $blueprint->table())
                ? $model
                : $this->makeModel($blueprint->schema(), $blueprint->table(), false);

            $related->withModel($model);
        }

        return $references;
    }

    /**
     * @param Model $model
     * @param string $name
     *
     * @return string
     * @throws FileNotFoundException
     */
    protected function prepareTemplate(Model $model, string $name): string
    {
        $defaultFile = $this->path([__DIR__, 'Templates', $name]);
        $file = $this->config($model->getBlueprint(), "*.template.$name", $defaultFile);

        return $this->files->get($file);
    }

    /**
     * @param string $template
     * @param Model $model
     *
     * @return mixed
     */
    protected function fillTemplate(string $template, Model $model)
    {
        $template = str_replace('{{namespace}}', $model->getBaseNamespace(), $template);
        $template = str_replace('{{class}}', $model->getClassName(), $template);

        $properties = $this->properties($model);
        $dependencies = $this->shortenAndExtractImportableDependencies($properties, $model);
        $template = str_replace('{{properties}}', $properties, $template);

        $parentClass = $model->getParentClass();
        $dependencies = array_merge($dependencies, $this->shortenAndExtractImportableDependencies($parentClass, $model));
        $template = str_replace('{{parent}}', $parentClass, $template);

        $body = $this->body($model);
        $dependencies = array_merge($dependencies, $this->shortenAndExtractImportableDependencies($body, $model));
        $template = str_replace('{{body}}', $body, $template);

        $imports = $this->imports(array_keys($dependencies), $model);
        $template = str_replace('{{imports}}', $imports, $template);

        return $template;
    }

    /**
     * Returns imports section for model.
     *
     * @param array $dependencies Array of imported classes
     * @param Model $model
     * @return string
     */
    private function imports(array $dependencies, Model $model): string
    {
        $imports = [];
        foreach ($dependencies as $dependencyClass) {
            // Skip when the same class
            if (trim($dependencyClass, "\\") == trim($model->getQualifiedUserClassName(), "\\")) {
                continue;
            }

            // Do not import classes from same namespace
            $inCurrentNamespacePattern = str_replace('\\', '\\\\', "/{$model->getBaseNamespace()}\\[a-zA-Z0-9_]*/");
            if (preg_match($inCurrentNamespacePattern, $dependencyClass)) {
                continue;
            }

            $imports[] = "use {$dependencyClass};";
        }

        sort($imports);

        return implode("\n", $imports);
    }

    /**
     * Extract and replace fully-qualified class names from placeholder.
     *
     * @param string $placeholder Placeholder to extract class names from. Rewrites value to content without FQN
     * @param Model $model
     *
     * @return array Extracted FQN
     */
    private function shortenAndExtractImportableDependencies(string &$placeholder, Model $model): array
    {
        $qualifiedClassesPattern = '/([\\\\a-zA-Z0-9_]*\\\\[\\\\a-zA-Z0-9_]*)/';
        $matches = [];
        $importableDependencies = [];
        if (preg_match_all($qualifiedClassesPattern, $placeholder, $matches)) {
            foreach ($matches[1] as $usedClass) {
                $namespacePieces = explode('\\', $usedClass);
                $className = array_pop($namespacePieces);

                //When same class name but different namespace, skip it.
                if (
                    $className == $model->getClassName() &&
                    trim(implode('\\', $namespacePieces), '\\') != trim($model->getNamespace(), '\\')
                ) {
                    continue;
                }

                $importableDependencies[trim($usedClass, '\\')] = true;
                $placeholder = str_replace($usedClass, $className, $placeholder);
            }
        }

        return $importableDependencies;
    }

    /**
     * @param Model $model
     *
     * @return string
     */
    protected function properties(Model $model): string
    {
        // Process property annotations
        $annotations = '';

        foreach ($model->getProperties() as $name => $hint) {
            $annotations .= $this->class->annotation('property', "$hint \$$name");
        }

        if ($model->hasRelations()) {
            // Add separation between model properties and model relations
            $annotations .= "\n * ";
        }

        foreach ($model->getRelations() as $name => $relation) {
            // TODO: Handle collisions, perhaps rename the relation.
            if ($model->hasProperty($name)) {
                continue;
            }
            $annotations .= $this->class->annotation('property', $relation->hint()." \$$name");
        }

        return $annotations;
    }

    /**
     * @param Model $model
     *
     * @return string
     */
    protected function body(Model $model): string
    {
        $body = '';

        foreach ($model->getTraits() as $trait) {
            $body .= $this->class->mixin($trait);
        }

        $excludedConstants = [];

        if ($model->hasCustomCreatedAtField()) {
            $body .= $this->class->constant('CREATED_AT', $model->getCreatedAtField());
            $excludedConstants[] = $model->getCreatedAtField();
        }

        if ($model->hasCustomUpdatedAtField()) {
            $body .= $this->class->constant('UPDATED_AT', $model->getUpdatedAtField());
            $excludedConstants[] = $model->getUpdatedAtField();
        }

        if ($model->hasCustomDeletedAtField()) {
            $body .= $this->class->constant('DELETED_AT', $model->getDeletedAtField());
            $excludedConstants[] = $model->getDeletedAtField();
        }

        if ($model->usesPropertyConstants()) {
            // Take all properties and exclude already added constants with timestamps.
            $properties = array_keys($model->getProperties());
            $properties = array_diff($properties, $excludedConstants);

            foreach ($properties as $property) {
                $constantName = Str::upper(Str::snake($property));
                $body .= $this->class->constant($constantName, $property);
            }
        }

        $body = trim($body, "\n");
        // Separate constants from fields only if there are constants.
        if (! empty($body)) {
            $body .= "\n";
        }

        // Append connection name when required
        if ($model->shouldShowConnection()) {
            $body .= $this->class->field('connection', $model->getConnectionName());
        }

        // When table is not plural, append the table name
        if ($model->needsTableName()) {
            $body .= $this->class->field('table', $model->getTableForQuery());
        }

        if ($model->hasCustomPrimaryKey()) {
            $body .= $this->class->field('primaryKey', $model->getPrimaryKey());
        }

        if ($model->doesNotAutoincrement()) {
            $body .= $this->class->field('incrementing', false, ['visibility' => 'public']);
        }

        if ($model->hasCustomPerPage()) {
            $body .= $this->class->field('perPage', $model->getPerPage());
        }

        if (! $model->usesTimestamps()) {
            $body .= $this->class->field('timestamps', false, ['visibility' => 'public']);
        }

        if ($model->hasCustomDateFormat()) {
            $body .= $this->class->field('dateFormat', $model->getDateFormat());
        }

        if ($model->doesNotUseSnakeAttributes()) {
            $body .= $this->class->field('snakeAttributes', false, ['visibility' => 'public static']);
        }

        if ($model->hasCasts()) {
            $body .= $this->class->field('casts', $model->getCasts(), ['before' => "\n"]);
        }

        if ($model->hasDates()) {
            $body .= $this->class->field('dates', $model->getDates(), ['before' => "\n"]);
        }

        if ($model->hasHidden() && $model->doesNotUseBaseFiles()) {
            $body .= $this->class->field('hidden', $model->getHidden(), ['before' => "\n"]);
        }

        if ($model->hasFillable() && $model->doesNotUseBaseFiles()) {
            $body .= $this->class->field('fillable', $model->getFillable(), ['before' => "\n"]);
        }

        if ($model->hasHints() && $model->usesHints()) {
            $body .= $this->class->field('hints', $model->getHints(), ['before' => "\n"]);
        }

        foreach ($model->getMutations() as $mutation) {
            $body .= $this->class->method($mutation->name(), $mutation->body(), ['before' => "\n"]);
        }

        foreach ($model->getRelations() as $constraint) {
            $body .= $this->class->method($constraint->name(), $constraint->body(), ['before' => "\n"]);
        }

        // Make sure there not undesired line breaks
        $body = trim($body, "\n");

        return $body;
    }

    /**
     * @param Model $model
     * @param array $custom
     *
     * @return string
     */
    protected function modelPath(Model $model, $custom = []): string
    {
        $modelsDirectory = $this->path(array_merge([$this->config($model->getBlueprint(), 'path')], $custom));

        if (! $this->files->isDirectory($modelsDirectory)) {
            $this->files->makeDirectory($modelsDirectory, 0755, true);
        }

        return $this->path([$modelsDirectory, $model->getClassName().'.php']);
    }

    /**
     * @param array $pieces
     *
     * @return string
     */
    protected function path(array $pieces): string
    {
        return implode(DIRECTORY_SEPARATOR, (array) $pieces);
    }

    /**
     * @param Model $model
     *
     * @return bool
     */
    public function needsUserFile(Model $model): bool
    {
        return ! $this->files->exists($this->modelPath($model)) && $model->usesBaseFiles();
    }

    /**
     * @param Model $model
     *
     * @throws FileNotFoundException
     */
    protected function createUserFile(Model $model)
    {
        $file = $this->modelPath($model);

        $template = $this->prepareTemplate($model, 'user_model');
        $template = str_replace('{{namespace}}', $model->getNamespace(), $template);
        $template = str_replace('{{class}}', $model->getClassName(), $template);
        $template = str_replace('{{imports}}', $this->formatBaseClasses($model), $template);
        $template = str_replace('{{parent}}', $this->getBaseClassName($model), $template);
        $template = str_replace('{{body}}', $this->userFileBody($model), $template);

        $this->files->put($file, $template);
    }

    /**
     * @param Model $model
     * @return string
     */
    private function formatBaseClasses(Model $model): string
    {
        return "use {$model->getBaseNamespace()}\\{$model->getClassName()} as {$this->getBaseClassName($model)};";
    }

    /**
     * @param Model $model
     * @return string
     */
    private function getBaseClassName(Model $model): string
    {
        return 'Base'.$model->getClassName();
    }

    /**
     * @param Model $model
     *
     * @return string
     */
    protected function userFileBody(Model $model): string
    {
        $body = '';

        if ($model->hasHidden()) {
            $body .= $this->class->field('hidden', $model->getHidden());
        }

        if ($model->hasFillable()) {
            $body .= $this->class->field('fillable', $model->getFillable(), ['before' => "\n"]);
        }

        // Make sure there is not an undesired line break at the end of the class body
        $body = ltrim(rtrim($body, "\n"), "\n");

        return $body;
    }

    /**
     * @param Blueprint|null $blueprint
     * @param string $key
     * @param mixed $default
     *
     * @return mixed|Config
     */
    public function config(Blueprint $blueprint = null, $key = null, $default = null)
    {
        if (is_null($blueprint)) {
            return $this->config;
        }

        return $this->config->get($blueprint, $key, $default);
    }
}
