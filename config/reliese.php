<?php

use Reliese\Analyser\Doctrine\MySqlDoctrineDatabaseAssistant;
use Reliese\Configuration\DatabaseAnalyserConfiguration;
use Reliese\Configuration\DatabaseBlueprintConfiguration;
use Reliese\Configuration\DataTransportGeneratorConfiguration;
use Reliese\Configuration\ModelDataMapGeneratorConfiguration;
use Reliese\Configuration\ModelGeneratorConfiguration;

/*
|--------------------------------------------------------------------------
| Configuration Notes
|--------------------------------------------------------------------------
|
| Where value matching occurs, then strings that that begin with '/^' and
| end with '$/' will be processed as a regular expression. Otherwise they
| are treated as a case sensitive string match.
|
*/

$all = '/^.*$/';

return [
    // The configuration profile to use unless a different one is specified on with the command line
    'default' => [
        // region Database Analyser Configuration
        /*
        |--------------------------------------------------------------------------
        | Database Connection Name
        |--------------------------------------------------------------------------
        |
        | Reliese package generates code based on database table structure. This
        | section defines which database connection will be analysed.
        |
        */
        DatabaseAnalyserConfiguration::class => [
            /*
            |--------------------------------------------------------------------------
            | Database Connection Name
            |--------------------------------------------------------------------------
            |
            | The name of the database connection from the Laravel database.php file
            | that should be used to populate schema and table blueprints
            |
            */
            'ConnectionName' => env('DB_CONNECTION'),

            'DoctrineDatabaseAssistantClass' => MySqlDoctrineDatabaseAssistant::class,
        ],
        // endregion Database Analyser Configuration


        // region Blueprint Configuration
        DatabaseBlueprintConfiguration::class => [
            /*
            |--------------------------------------------------------------------------
            | Database Blueprint Filters
            |--------------------------------------------------------------------------
            |
            | The IncludeByDefault value can be inverted by specifying Except
            | Conditions.
            |
            | This allows the config to either default to either "include all" or
            | "exclude all".
            |
            | Except conditions allow selective inversion of the default.
            |
            | Examples:
            | - "Include everything except the audit schema"
            | - "Exclude everything except specific tables from a specific schema"
            |
            | Except conditions use an array of strings to match against schema, table,
            | and column names.
            |
            | To exclude a schema, spcifiy on a schema match
            | To exclude a table
            |
            | When one of these strings begins with '/^' and ends with '$/' then it is
            | treated as a Regular expresion instead of a string match.
            |
            | If a Schema is excluded, then no table within that schema can be included.
            | The same applies for Columns within an excluded Table.
            |
            */
            'Filters' => [
                'IncludeByDefault' => true,
                'Except' => [
                    // except everything in schema
                    ['schemas' => ['information_schema', 'performance_schema', 'mysql', 'sys']],
                    // except audit.log_.* tables
                    ['schemas' => ['audit'], 'tables' => ['/^log_.*$/']],
                    // except any table that ends in migrations or matches 'phinx' on all schemas
                    ['schemas' => [$all], 'tables' => ['/^.*migrations$/', 'phinx']],
                    // except soft delete columns on all tables for all schemas
                    ['schemas' => [$all], 'tables' => [$all], 'columns' => ['deleted_on']]
                ],
            ],
            /*
            |--------------------------------------------------------------------------
            | Additional Relationships
            |--------------------------------------------------------------------------
            |
            | This section allows for relationships to be manually defined even if they
            | are not present in the database foreign key structures
            |
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

            /*
            |--------------------------------------------------------------------------
            | Model Files Location
            |--------------------------------------------------------------------------
            |
            | We need a location to store your new generated files. All files will be
            | placed within this directory. When you turn on base files, they will
            | be placed within a Base directory inside this location.
            |
            */

            'Path' => app_path('Models'),

            /*
            |--------------------------------------------------------------------------
            | Model Namespace
            |--------------------------------------------------------------------------
            |
            | Every generated model will belong to this namespace. It is suggested
            | that this namespace should follow PSR-4 convention and be very
            | similar to the path of your models defined above.
            |
            */

            'Namespace' => 'App\Models',

            /*
            |--------------------------------------------------------------------------
            | Model Class Suffix
            |--------------------------------------------------------------------------
            */
            'ClassSuffix' => '',

            /*
            |--------------------------------------------------------------------------
            | Parent Class Prefix
            |--------------------------------------------------------------------------
            */
            'ParentClassPrefix' => 'Abstract',

            /*
            |--------------------------------------------------------------------------
            | Parent Class
            |--------------------------------------------------------------------------
            |
            | All Eloquent models should inherit from Eloquent Model class. However,
            | you can define a custom Eloquent model that suits your needs.
            | As an example one custom model has been added for you which
            | will allow you to create custom database castings.
            |
            */

            'Parent' => Illuminate\Database\Eloquent\Model::class,

            /*
            |--------------------------------------------------------------------------
            | Traits
            |--------------------------------------------------------------------------
            |
            | Sometimes you may want to append certain traits to all your models.
            | If that is what you need, you may list them bellow.
            |
            */

            'Traits' => [
                // Laravel\Scout\Searchable::class
            ],

            /*
            |--------------------------------------------------------------------------
            | Model Connection
            |--------------------------------------------------------------------------
            |
            | If you wish your models had appended the connection from which they
            | were generated, you should set this value to true and your
            | models will have the connection property filled.
            |
            */

            'AppendConnection' => false,

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            |
            | If your tables have CREATED_AT and UPDATED_AT timestamps you may
            | enable them and your models will fill their values as needed.
            | You can also specify which fields should be treated as timestamps
            | in case you don't follow the naming convention Eloquent uses.
            | If your table doesn't have these fields, timestamps will be
            | disabled for your model.
            |
            */

            'timestamps' => true,

            // 'timestamps' => [
            //     'enabled' => true,
            //     'fields' => [
            //         'CREATED_AT' => 'created_at',
            //         'UPDATED_AT' => 'updated_at',
            //     ]
            // ],

            /*
            |--------------------------------------------------------------------------
            | Soft Deletes
            |--------------------------------------------------------------------------
            |
            | If your tables support soft deletes with a DELETED_AT attribute,
            | you can enable them here. You can also specify which field
            | should be treated as a soft delete attribute in case you
            | don't follow the naming convention Eloquent uses.
            | If your table doesn't have this field, soft deletes will be
            | disabled for your model.
            |
            */

            'soft_deletes' => true,

            // 'soft_deletes' => [
            //     'enabled' => true,
            //     'field' => 'deleted_at',
            // ],

            /*
            |--------------------------------------------------------------------------
            | Date Format
            |--------------------------------------------------------------------------
            |
            | Here you may define your models' date format. The following format
            | is the default format Eloquent uses. You won't see it in your
            | models unless you change it to a more convenient value.
            |
            */

            'date_format' => 'Y-m-d H:i:s',

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            |
            | Here you may define how many models Eloquent should display when
            | paginating them. The default number is 15, so you might not
            | see this number in your models unless you change it.
            |
            */

            'per_page' => 15,

            /*
            |--------------------------------------------------------------------------
            | Base Files
            |--------------------------------------------------------------------------
            |
            | By default, your models will be generated in your models path, but
            | when you generate them again they will be replaced by new ones.
            | You may want to customize your models and, at the same time, be
            | able to generate them as your tables change. For that, you
            | can enable base files. These files will be replaced whenever
            | you generate them, but your customized files will not be touched.
            |
            */

            'base_files' => false,

            /*
            |--------------------------------------------------------------------------
            | Snake Attributes
            |--------------------------------------------------------------------------
            |
            | Eloquent treats your model attributes as snake cased attributes, but
            | if you have camel-cased fields in your database you can disable
            | that behaviour and use camel case attributes in your models.
            |
            */

            'snake_attributes' => true,

            /*
            |--------------------------------------------------------------------------
            | Indent options
            |--------------------------------------------------------------------------
            |
            | As default indention is done with tabs, but you can change it by setting
            | this to the amount of spaces you that you want to use for indentation.
            | Usually you will use 4 spaces instead of tabs.
            |
            */

            'indent_with_space' => 0,

            /*
            |--------------------------------------------------------------------------
            | Qualified Table Names
            |--------------------------------------------------------------------------
            |
            | If some of your tables have cross-database relationships (probably in
            | MySQL), you can make sure your models take into account their
            | respective database schema.
            |
            | Can Either be NULL, FALSE or TRUE
            | TRUE: Schema name will be prepended on the table
            | FALSE:Table name will be set without schema name.
            | NULL: Table name will follow laravel pattern,
            |   i.e if class name(plural) matches table name, then table name will not be added
            */

            'qualified_tables' => false,

            /*
            |--------------------------------------------------------------------------
            | Hidden Attributes
            |--------------------------------------------------------------------------
            |
            | When casting your models into arrays or json, the need to hide some
            | attributes sometimes arise. If your tables have some fields you
            | want to hide, you can define them bellow.
            | Some fields were defined for you.
            |
            */

            'hidden' => [
                '*secret*', '*password', '*token',
            ],

            /*
            |--------------------------------------------------------------------------
            | Mass Assignment Guarded Attributes
            |--------------------------------------------------------------------------
            |
            | You may want to protect some fields from mass assignment. You can
            | define them bellow. Some fields were defined for you.
            | Your fillable attributes will be those which are not in the list
            | excluding your models' primary keys.
            |
            */

            'guarded' => [
                // 'created_by', 'updated_by'
            ],

            /*
            |--------------------------------------------------------------------------
            | Casts
            |--------------------------------------------------------------------------
            |
            | You may want to specify which of your table fields should be casted as
            | something different than a string. For instance, you may want a
            | text field be casted as an array or and object.
            |
            | You may define column patterns which will be casted using the value
            | assigned. We have defined some fields for you. Feel free to
            | modify them to fit your needs.
            |
            */

            'casts' => [
                '*_json' => 'json',
            ],

            // This section has moved to the Blueprint Scope section
            //            /*
            //            |--------------------------------------------------------------------------
            //            | Excluded Tables
            //            |--------------------------------------------------------------------------
            //            |
            //            | When performing the generation of models you may want to skip some of
            //            | them, because you don't want a model for them or any other reason.
            //            | You can define those tables bellow. The migrations table was
            //            | filled for you, since you may not want a model for it.
            //            |
            //            */
            //
            //            'except' => [
            //                'migrations',
            //            ],
            // This section has moved to the Blueprint Scope section
            //            /*
            //            |--------------------------------------------------------------------------
            //            | Specified Tables
            //            |--------------------------------------------------------------------------
            //            |
            //            | You can specify specific tables. This will generate the models only
            //            | for selected tables, ignoring the rest.
            //            |
            //            */
            //
            //            'only' => [
            //                // 'users',
            //            ],

            /*
            |--------------------------------------------------------------------------
            | Table Prefix
            |--------------------------------------------------------------------------
            |
            | If you have a prefix on your table names but don't want it in the model
            | and relation names, specify it here.
            |
            */

            'table_prefix' => '',

            /*
            |--------------------------------------------------------------------------
            | Lower table name before doing studly
            |--------------------------------------------------------------------------
            |
            | If tables names are capitalised using studly produces incorrect name
            | this can help fix it ie TABLE_NAME now becomes TableName
            |
            */

            'lower_table_name_first' => false,

            /*
            |--------------------------------------------------------------------------
            | Model Names
            |--------------------------------------------------------------------------
            |
            | By default the generator will create models with names that match your tables.
            | However, if you wish to manually override the naming, you can specify a mapping
            | here between table and model names.
            |
            | Example:
            |   A table called 'billing_invoices' will generate a model called `BillingInvoice`,
            |   but you'd prefer it to generate a model called 'Invoice'. Therefore, you'd add
            |   the following array key and value:
            |     'billing_invoices' => 'Invoice',
            */

            'model_names' => [
                // example to rename the database table logins as the User model...
                // ['schema' => $all, 'table' => 'logins', 'model' => 'User']
            ],

            /*
            |--------------------------------------------------------------------------
            | Relation Name Strategy
            |--------------------------------------------------------------------------
            |
            | How the relations should be named in your models.
            |
            | 'related'     Use the related table as the relation name.
            |               (post.author --> user.id)
            |                   generates Post::user() and User::posts()
            |
            | 'foreign_key' Use the foreign key as the relation name.
            |               This can help to provide more meaningful relationship names, and avoids naming conflicts
            |               if you have more than one relationship between two tables.
            |                   (post.author_id --> user.id)
            |                       generates Post::author() and User::posts_where_author()
            |                   (post.editor_id --> user.id)
            |                       generates Post::editor() and User::posts_where_editor()
            |               ID suffixes can be omitted from foreign keys.
            |                   (post.author --> user.id)
            |                   (post.editor --> user.id)
            |                       generates the same as above.
            |               Where the foreign key matches the related table name, it behaves as per the 'related' strategy.
            |                   (post.user_id --> user.id)
            |                       generates Post::user() and User::posts()
            */

            'relation_name_strategy' => 'related',
            // 'relation_name_strategy' => 'foreign_key',

            /*
             |--------------------------------------------------------------------------
             | Determines need or not to generate constants with properties names like
             |
             | ...
             | const AGE = 'age';
             | const USER_NAME = 'user_name';
             | ...
             |
             | that later can be used in QueryBuilder like
             |
             | ...
             | $builder->select([User::USER_NAME])->where(User::AGE, '<=', 18);
             | ...
             |
             | that helps to avoid typos in strings when typing field names and allows to use
             | code competition with available model's field names.
             */
            'with_property_constants' => false,

            /*
            |--------------------------------------------------------------------------
            | Disable Pluralization Name
            |--------------------------------------------------------------------------
            |
            | You can disable pluralization tables and relations
            |
            */
            'pluralize' => true,

            /*
            |--------------------------------------------------------------------------
            | Disable Pluralization Except For Certain Tables
            |--------------------------------------------------------------------------
            |
            | You can enable pluralization for certain tables
            |
            */
            'override_pluralize_for' => [

            ],
        ],
        // endregion Model Generator Config
        // region Data Transport Generator Config
        DataTransportGeneratorConfiguration::class => [
            'Path' => app_path().'/DataTransportObjects',
            'Namespace' => 'App\DataTransportObjects',
            'ClassSuffix' => 'Dto',
            'ParentClassPrefix' => 'Abstract',
        ],
        // endregion Data Transport Generator Config
        // region Data Map Generator Config
        ModelDataMapGeneratorConfiguration::class => [
            'Path' => app_path().'/DataMaps/PrimaryDatabase',
            'Namespace' => 'App\DataMaps\PrimaryDatabase',
            'ClassSuffix' => 'Map',
            'ParentClassPrefix' => 'Abstract',
        ],
        // endregion Data Map Generator Config
    ]
];
