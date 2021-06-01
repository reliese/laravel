<?php
use Reliese\Analyser\Doctrine\MySqlDatabaseVendorAdapter;
use Reliese\Configuration\Sections\CodeFormattingConfiguration;
use Reliese\Configuration\Sections\DatabaseAnalyserConfiguration;
use Reliese\Configuration\Sections\DatabaseBlueprintConfiguration;
use Reliese\Configuration\Sections\DataTransportObjectGeneratorConfiguration;
use Reliese\Configuration\Sections\ModelDataMapGeneratorConfiguration;
use Reliese\Configuration\Sections\DataAccessClassGeneratorConfiguration;
use Reliese\Configuration\Sections\DataAttributeGeneratorConfiguration;
use Reliese\Configuration\Sections\DataTransportCollectionGeneratorConfiguration;
use Reliese\Configuration\Sections\ModelGeneratorConfiguration;
use Reliese\Configuration\Sections\ValidatorGeneratorConfiguration;

/* --------------------------------------------------------------------------
 * Configuration Notes
 * --------------------------------------------------------------------------
 *
 * Where value matching occurs, then strings that that begin with '/^' and
 * end with '$/' will be processed as a regular expression. Otherwise they
 * are treated as a case sensitive string match.
 * --------------------------------------------------------------------------
 * Support for multiple connections
 * --------------------------------------------------------------------------
 * The configuration returned by this file is a collection of configuration
 * profiles. At least one configuration named 'default' must be returned.
 *
 * However, multiple can be returned, and the command line options used to
 * specify which configuration should be used for each command execution.
 *
 * This allows a a 'default' and 'alternate' profile to identify different
 * database connections. Those database connections are used when performing
 * database analysis.
 *
 * The output of the database analysis is a set of Database, Schema, Table,
 * Column, Index, and ForeignKey Blueprints.
 *
 * Those Blueprints are then used to drive code generation for Models, Dtos,
 * Maps, DataAccess, Validators and more.
 *
 */

$all = '/^.*$/';
return [
    // The configuration profile to use unless a different one is specified on with the command line
    'default' => [
        CodeFormattingConfiguration::class => [
          'IndentationSymbol' => '    ',
        ],
        // region Database Analyser Configuration
        /* --------------------------------------------------------------------------
         * Database Connection Name
         * --------------------------------------------------------------------------
         * Reliese package generates code based on database table structure. This
         * section defines which database connection will be analysed.
         */
        DatabaseAnalyserConfiguration::class => [
            /* --------------------------------------------------------------------------
             * Database Connection Name
             * --------------------------------------------------------------------------
             * The name of the database connection from the Laravel database.php file
             * that should be used to populate schema and table blueprints
             */
            'ConnectionName' => env('DB_CONNECTION'),
            'DoctrineDatabaseAssistantClass' => MySqlDatabaseVendorAdapter::class,
        ],
        // endregion Database Analyser Configuration

        // region Blueprint Configuration
        DatabaseBlueprintConfiguration::class => [
            /* --------------------------------------------------------------------------
             * Database Blueprint Filters
             * --------------------------------------------------------------------------
             * The IncludeByDefault value can be inverted by specifying Except Conditions.
             *
             * This allows the config to either default to either "include all" or
             * "exclude all".
             *
             * Except conditions allow selective inversion of the default.
             *
             * Examples:
             * - "Include everything except the audit schema"
             * - "Exclude everything except specific tables from a specific schema"
             *
             * Except conditions use an array of strings to match against schema, table,
             * and column names.
             *
             * To exclude a schema, spcifiy on a schema match
             * To exclude a table
             *
             * When one of these strings begins with '/^' and ends with '$/' then it is
             * treated as a Regular expresion instead of a string match.
             *
             * If a Schema is excluded, then no table within that schema can be included.
             * The same applies for Columns within an excluded Table.
             */
            'Filters' => [
                'IncludeByDefault' => true,
                'Except' => [
                    // except everything in schema
                    ['schemas' => ['information_schema', 'performance_schema', 'mysql', 'sys']],
                    // except audit.log_.* tables
                    //['schemas' => ['audit'], 'tables' => ['/^log_.*$/']],
                    // except any table that ends in migrations or matches 'phinx' on all schemas
                    ['schemas' => [$all], 'tables' => ['/^.*migrations$/', 'phinx']],
                    // except soft delete columns on all tables for all schemas
                    //['schemas' => [$all], 'tables' => [$all], 'columns' => ['deleted_on']]
                ],
            ],
            /* --------------------------------------------------------------------------
             * Additional Relationships
             * --------------------------------------------------------------------------
             *
             * This section allows for relationships to be manually defined even if they
             * are not present in the database foreign key structures
             */
            'AdditionalRelationships' => [
                //'AdditionalFkName' => [
                //    'Referenced' => ['Schema' => 'authentication', 'Table' => 'users', 'Columns' => ['id']],
                //    'Referencing' => ['Schema' => 'audit', 'Table' => 'login_attempts', 'Columns' => ['user_id']],
                //]
            ],
        ],
        // endregion Blueprint Configuration

        // region Model Generator Config
        ModelGeneratorConfiguration::class => [

            /* --------------------------------------------------------------------------
             *  Class Namespace
             * --------------------------------------------------------------------------
             * The PSR-4 location in which to create the class files that extend the
             * automatically generated class files. Modifications to files in this
             * namespace will not be overwritten.
             */
            'ClassNamespace' => 'app\Models\PrimaryDatabase',

            /* --------------------------------------------------------------------------
             * Class Prefix and Suffix
             * --------------------------------------------------------------------------
             * ClassPrefix . CamelCase(Singular(table_name)) . ClassSuffix
            */
            'ClassPrefix' => '',
            'ClassSuffix' => 'Model',

            /* --------------------------------------------------------------------------
             *  Generated Class Namespace
             * --------------------------------------------------------------------------
             * The PSR-4 location in which to create the automatically generated class
             * files. MODIFICATIONS TO FILES IN THIS NAMESPACE WILL BE OVERWRITTEN.
             */
            'GeneratedClassNamespace' => 'app\Models\PrimaryDatabase\Generated',
            /* --------------------------------------------------------------------------
             * Generated Class Prefix and Suffix
             * --------------------------------------------------------------------------
             * GeneratedClassPrefix . CamelCase(Singular(table_name)) . GeneratedClassSuffix
             */
            'GeneratedClassPrefix' => 'Abstract',
            'GeneratedClassSuffix' => 'DataAccess',

            /* --------------------------------------------------------------------------
             * Parent Class
             * --------------------------------------------------------------------------
             * All Eloquent models should inherit from Eloquent Model class. However,
             * you can define a custom Eloquent model that suits your needs.
             * As an example one custom model has been added for you which
             * will allow you to create custom database castings.
             */
            'GeneratedClassParentClass' => Illuminate\Database\Eloquent\Model::class,
        ],
        // endregion Model Generator Config

        // region Data Access Generator Config
        DataAccessClassGeneratorConfiguration::class => [

            /* --------------------------------------------------------------------------
             *  Class Namespace
             * --------------------------------------------------------------------------
             * The PSR-4 location in which to create the class files that extend the
             * automatically generated class files. Modifications to files in this
             * namespace will not be overwritten.
             */
            'ClassNamespace' => 'app\DataAccess\PrimaryDatabase',
            /* --------------------------------------------------------------------------
             * Class Prefix and Suffix
             * --------------------------------------------------------------------------
             * ClassPrefix . CamelCase(Singular(table_name)) . ClassSuffix
            */
            'ClassPrefix' => '',
            'ClassSuffix' => 'DataAccess',
            /* --------------------------------------------------------------------------
             *  Generated Class Namespace
             * --------------------------------------------------------------------------
             * The PSR-4 location in which to create the automatically generated class
             * files. MODIFICATIONS TO FILES IN THIS NAMESPACE WILL BE OVERWRITTEN.
             */
            'GeneratedClassNamespace' => 'app\DataAccess\PrimaryDatabase\Generated',
            /* --------------------------------------------------------------------------
             * Generated Class Prefix and Suffix
             * --------------------------------------------------------------------------
             * GeneratedClassPrefix . CamelCase(Singular(table_name)) . GeneratedClassSuffix
             */
            'GeneratedClassPrefix' => 'Abstract',
            'GeneratedClassSuffix' => 'DataAccess',
        ],
        // endregion Data Access Generator Config

        // region Data Transport Generator Config
        DataTransportObjectGeneratorConfiguration::class => [

            /* --------------------------------------------------------------------------
             *  Class Namespace
             * --------------------------------------------------------------------------
             * The PSR-4 location in which to create the class files that extend the
             * automatically generated class files. Modifications to files in this
             * namespace will not be overwritten.
             */
            'ClassNamespace' => 'app\DataTransport\Objects\PrimaryDatabase',
            /* --------------------------------------------------------------------------
             * Class Prefix and Suffix
             * --------------------------------------------------------------------------
             * ClassPrefix . CamelCase(Singular(table_name)) . ClassSuffix
            */
            'ClassPrefix' => '',
            'ClassSuffix' => 'Dto',
            /* --------------------------------------------------------------------------
             *  Generated Class Namespace
             * --------------------------------------------------------------------------
             * The PSR-4 location in which to create the automatically generated class
             * files. MODIFICATIONS TO FILES IN THIS NAMESPACE WILL BE OVERWRITTEN.
             */
            'GeneratedClassNamespace' => 'app\DataTransport\Objects\PrimaryDatabase\Generated',
            /* --------------------------------------------------------------------------
             * Generated Class Prefix and Suffix
             * --------------------------------------------------------------------------
             * GeneratedClassPrefix . CamelCase(Singular(table_name)) . GeneratedClassSuffix
             */
            'GeneratedClassPrefix' => 'Abstract',
            'GeneratedClassSuffix' => 'Dto',
            'UseValueStateTracking' => true,
            'ObservableProperties' => [
                'BeforeChange' => false,
                'AfterChange' => false,
            ],
        ],
        // endregion Data Transport Generator Config

        // region Data Transport Collection Generator Config
        DataTransportCollectionGeneratorConfiguration::class => [

            /* --------------------------------------------------------------------------
             *  Class Namespace
             * --------------------------------------------------------------------------
             * The PSR-4 location in which to create the class files that extend the
             * automatically generated class files. Modifications to files in this
             * namespace will not be overwritten.
             */
            'ClassNamespace' => 'app\DataTransport\Collections\PrimaryDatabase',
            /* --------------------------------------------------------------------------
             * Class Prefix and Suffix
             * --------------------------------------------------------------------------
             * ClassPrefix . CamelCase(Singular(table_name)) . ClassSuffix
            */
            'ClassPrefix' => '',
            'ClassSuffix' => 'Collection',
            /* --------------------------------------------------------------------------
             *  Generated Class Namespace
             * --------------------------------------------------------------------------
             * The PSR-4 location in which to create the automatically generated class
             * files. MODIFICATIONS TO FILES IN THIS NAMESPACE WILL BE OVERWRITTEN.
             */
            'GeneratedClassNamespace' => 'app\DataTransport\Collections\PrimaryDatabase\Generated',
            /* --------------------------------------------------------------------------
             * Generated Class Prefix and Suffix
             * --------------------------------------------------------------------------
             * GeneratedClassPrefix . CamelCase(Singular(table_name)) . GeneratedClassSuffix
             */
            'GeneratedClassPrefix' => 'Abstract',
            'GeneratedClassSuffix' => 'Collection',
        ],
        // endregion Data Transport Collection Generator Config

        // region Data Map Generator Config
        ModelDataMapGeneratorConfiguration::class => [
            /* --------------------------------------------------------------------------
             *  Class Namespace
             * --------------------------------------------------------------------------
             * The PSR-4 location in which to create the class files that extend the
             * automatically generated class files. Modifications to files in this
             * namespace will not be overwritten.
             */
            'ClassNamespace' => 'app\DataMaps\PrimaryDatabase',
            /* --------------------------------------------------------------------------
             * Class Prefix and Suffix
             * --------------------------------------------------------------------------
             * ClassPrefix . CamelCase(Singular(table_name)) . ClassSuffix
            */
            'ClassPrefix' => '',
            'ClassSuffix' => 'Map',
            /* --------------------------------------------------------------------------
             *  Generated Class Namespace
             * --------------------------------------------------------------------------
             * The PSR-4 location in which to create the automatically generated class
             * files. MODIFICATIONS TO FILES IN THIS NAMESPACE WILL BE OVERWRITTEN.
             */
            'GeneratedClassNamespace' => 'app\Mapping\PrimaryDatabase\Generated',
            /* --------------------------------------------------------------------------
             * Generated Class Prefix and Suffix
             * --------------------------------------------------------------------------
             * GeneratedClassPrefix . CamelCase(Singular(table_name)) . GeneratedClassSuffix
             */
            'GeneratedClassPrefix' => 'Abstract',
            'GeneratedClassSuffix' => 'Map',
            'AccessorTraitNamespace' => 'app\Mapping\Accessors\PrimaryDatabase',
        ],
        // endregion Data Map Generator Config

        // region Validator Generator Config
        ValidatorGeneratorConfiguration::class => [
            /* --------------------------------------------------------------------------
             *  Class Namespace
             * --------------------------------------------------------------------------
             * The PSR-4 location in which to create the class files that extend the
             * automatically generated class files. Modifications to files in this
             * namespace will not be overwritten.
             */
            'ClassNamespace' => 'app\DataMaps\PrimaryDatabase',
            /* --------------------------------------------------------------------------
             * Class Prefix and Suffix
             * --------------------------------------------------------------------------
             * ClassPrefix . CamelCase(Singular(table_name)) . ClassSuffix
            */
            'ClassPrefix' => '',
            'ClassSuffix' => 'Validator',
            /* --------------------------------------------------------------------------
             *  Generated Class Namespace
             * --------------------------------------------------------------------------
             * The PSR-4 location in which to create the automatically generated class
             * files. MODIFICATIONS TO FILES IN THIS NAMESPACE WILL BE OVERWRITTEN.
             */
            'GeneratedClassNamespace' => 'app\Validation\PrimaryDatabase\Generated',
            /* --------------------------------------------------------------------------
             * Generated Class Prefix and Suffix
             * --------------------------------------------------------------------------
             * GeneratedClassPrefix . CamelCase(Singular(table_name)) . GeneratedClassSuffix
             */
            'GeneratedClassPrefix' => 'Abstract',
            'GeneratedClassSuffix' => 'Validator',
            /* --------------------------------------------------------------------------
             * Accessor Trait Namespace
             * --------------------------------------------------------------------------
             * Generates Accessor traits like the following example in order to simplify
             * accessing objects from the service container.
             *
             * Example:
             *   Given the table name `users`
             *   The generator creates the class `UserValidator`
             *   The generator creates the trait `WithUserValidator`
             */
            'AccessorTraitNamespace' => 'app\Validation\ValidatorAccessors\PrimaryDatabase',
            /* --------------------------------------------------------------------------
             * Accessor Trait Prefix and Suffix
             * --------------------------------------------------------------------------
             * AccessorClassPrefix . ValidatorClassName . AccessorClassSuffix
             */
            'AccessorTraitPrefix' => 'With',
            'AccessorTraitSuffix' => '',
        ],
        // endregion Validator Generator Config
    ]
];
