# Reliese Laravel

[![StyleCI](https://styleci.io/repos/71080508/shield?style=flat)](https://styleci.io/repos/71080508)
[![Build Status](https://travis-ci.org/reliese/laravel.svg?branch=master)](https://travis-ci.org/reliese/laravel)
[![Latest Stable Version](https://poser.pugx.org/reliese/laravel/v/stable)](https://packagist.org/packages/reliese/laravel)
[![Total Downloads](https://poser.pugx.org/reliese/laravel/downloads)](https://packagist.org/packages/reliese/laravel)
[![Latest Unstable Version](https://poser.pugx.org/reliese/laravel/v/unstable)](https://packagist.org/packages/reliese/laravel)
[![License](https://poser.pugx.org/reliese/laravel/license)](https://packagist.org/packages/reliese/laravel)

Reliese Laravel is a collection of Laravel Components which aim is 
to help the development process of Laravel applications by 
providing some convenient code-generation capabilities.

## How does it work?

This package expects that you are using Laravel 5.1 or above.
You will need to import the `reliese/laravel` package via composer:

```shell
composer require reliese/laravel
```

### Configuration

Add the service provider to your `config/app.php` file within the `providers` key:

```php
// ...
'providers' => [
    /*
     * Package Service Providers...
     */

    Reliese\Coders\CodersServiceProvider::class,
],
// ...
```
### Configuration for local environment only

If you wish to enable generators only for your local environment, you should install it via composer using the --dev option like this:

```shell
composer require reliese/laravel --dev
```

Then you'll need to register the provider in `app/Providers/AppServiceProvider.php` file.

```php
public function register()
{
    if ($this->app->environment() == 'local') {
        $this->app->register(\Reliese\Coders\CodersServiceProvider::class);
    }
}
```

## Models

![Generating models with artisan](https://cdn-images-1.medium.com/max/800/1*hOa2QxORE2zyO_-ZqJ40sA.png "Making artisan code my Eloquent models")

Add the `models.php` configuration file to your `config` directory:

```shell
php artisan vendor:publish --tag=reliese-models
```

### Usage

Assuming you have already configured your database, you are now all set to go.

- Let's scaffold some of your models from your default connection.

```shell
php artisan code:models
```

- You can scaffold a specific table like this:

```shell
php artisan code:models --table=users
```

- You can also specify the connection:

```shell
php artisan code:models --connection=mysql
```

- If you are using a MySQL database, you can specify which schema you want to scaffold:

```shell
php artisan code:models --schema=shop
```

### Customizing Model Scaffolding

To change the scaffolding behaviour you can make `config/models.php` configuration file
fit your database needs. [Check it out](https://github.com/reliese/laravel/blob/master/config/models.php) ;-)

### Tips

#### 1. Keeping model changes

You may want to generate your models as often as you change your database. In order
not to lose you own model changes, you should set `base_files` to `true` in your `config/models.php`.

When you enable this feature your models will inherit their base configurations from
base models. You should avoid adding code to your base models, since you
will lose all changes when they are generated again.

> Note: You will end up with to models for the same table and you may think it is a horrible idea 
to have two classes for the same thing. However, it is up to you
to decide whether this approach gives value to your project :-)

#### Support

For the time being, this package only supports MySQL databases. Support for other databases will be added soon.
