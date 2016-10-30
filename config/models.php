<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Configurations
    |--------------------------------------------------------------------------
    |
    | In this section you may define the default configuration for each model
    | that will be generated from any database.
    |
    */

    '*' => [

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

        'path' => app_path('Models'),

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

        'namespace' => 'App\Models',

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

        'parent' => Reliese\Database\Eloquent\Model::class,

        /*
        |--------------------------------------------------------------------------
        | Traits
        |--------------------------------------------------------------------------
        |
        | Sometimes you may want to append certain traits to all your models.
        | If that is what you need, you may list them bellow.
        | As an example we have a BitBooleans trait which will treat MySQL bit
        | data type as booleans. You might probably not need it, but it is
        | an example of how you can customize your models.
        |
        */

        'use' => [
            // Reliese\Database\Eloquent\BitBooleans::class,
            // Reliese\Database\Eloquent\BlamableBehavior::class,
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

        'connection' => false,

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
        | Qualified Table Names
        |--------------------------------------------------------------------------
        |
        | If some of your tables have cross-database relationships (probably in
        | MySQL), you can make sure your models take into account their
        | respective database schema.
        |
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

        /*
        |--------------------------------------------------------------------------
        | Excluded Tables
        |--------------------------------------------------------------------------
        |
        | When performing the generation of models you may want to skip some of
        | them, because you don't want a model for them or any other reason.
        | You can define those tables bellow. The migrations table was
        | filled for you, since you may not want a model for it.
        |
        */

        'except' => [
            'migrations',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Specifics
    |--------------------------------------------------------------------------
    |
    | In this section you may define the default configuration for each model
    | that will be generated from a specific database. You can also nest
    | table specific configurations.
    | These values will override those defined in the section above.
    |
    */

    // 'shop' => [
    //     'path' => app_path(),
    //     'namespace' => 'App',
    //     'snake_attributes' => false,
    //     'qualified_tables' => true,
    //     'use' => [
    //         Reliese\Database\Eloquent\BitBooleans::class,
    //     ],
    //     'except' => ['migrations'],
    //      // Table Specifics Bellow:
    //     'user' => [
    //      // Don't use any default trait
    //         'use' => [],
    //     ]
    // ],
];
