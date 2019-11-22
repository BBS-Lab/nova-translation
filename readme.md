# Laravel Nova Translation

[![StyleCI](https://styleci.io/repos/221661972/shield)](https://styleci.io/repos/221661972)
[![Quality Score](https://img.shields.io/scrutinizer/g/bbs-lab/nova-translation.svg?style=flat-square)](https://scrutinizer-ci.com/g/bbs-lab/nova-translation)

## Contents

- [Installation](#installation)
- [Usage](#usage)
    - [Locale resource](#locale-resource)
    - [TranslationMatrix tool](#translationmatrix-tool)
- [GraphQL](#graphql)

## Installation

You can install the nova tool in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:


``` bash
composer require bbs-lab/nova-translation
```

The service provider will automatically get registered. Or you may manually add the service provider in your `config/app.php` file:

```php
'providers' => [
    // ...
    BBSLab\NovaTranslation\NovaTranslationServiceProvider::class,
],
```

You can tailor default in your application by running:

```bash
php artisan vendor:publish --provider="BBSLab\NovaTranslation\NovaTranslationServiceProvider::class"
```

You need to run migrations and seeds Locales.

```bash
php artisan migrate
```

## Models setup

// @TODO... Explain 

 * `use Traits\Translatable`
 
 * `auto_synced_models` in config.php
 
 * ...

## Usage

### TranslationMatrix tool

You must register the translation matrix backend tool with [Nova](https://nova.laravel.com):

```php
// app/Providers/NovaServiceProvider.php

public function tools()
{
    return [
        new \BBSLab\NovaTranslation\Tools\TranslationMatrix,
    ];
}
```

### Locale resource

And you can add the Locale [Nova](https://nova.laravel.com) Resource within your application:

```php
// app/Nova/Locale.php

<?php

namespace App\Nova;

use App\Helpers\StaticLabel;
use BBSLab\NovaTranslation\Resources\Locale as BaseResource;

class Locale extends BaseResource
{
    /**
     * {@inheritdoc}
     */
    public static $group = StaticLabel::GROUP_ADMINISTRATION;
}
```

## GraphQL

If your using [Lighthouse PHP](https://lighthouse-php.com) you can add some default Directive and endpoints for `Locale` and `Label`.

### Directive `@translation`

You need to add package Directives path to your lighthouse.php configuration file:

```php
// config/lighthouse.php

'namespaces' => [
    // ...
    'directives' => ['App\\GraphQL\\Directives', 'BBSLab\\NovaTranslation\\GraphQL\\Directives'],
],
```

### Schema

You can include those in your existing schema:

```graphql
#import ../../../vendor/bbs-lab/nova-translation/src/Http/GraphQL/Locale/*.graphql
#import ../../../vendor/bbs-lab/nova-translation/src/Http/GraphQL/Label/*.graphql
```
