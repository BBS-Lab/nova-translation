# Laravel Nova Translation

[![StyleCI](https://styleci.io/repos/220784911/shield)](https://styleci.io/repos/220784911)
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
    BBS\Nova\Translation\ServiceProvider::class,
],
```

You can tailor default in your application by running:

```bash
php artisan vendor:publish --provider="BBSLab\Nova\Translation\ServiceProvider::class"
```

You need to run migrations 

```bash
php artisan migrate
```

## Usage

### TranslationMatrix tool

You must register the translation matrix backend tool with [Nova](https://nova.laravel.com):

```php
// app/Providers/NovaServiceProvider.php

public function tools()
{
    return [
        new \BBS\Nova\Translation\Tools\TranslationMatrix,
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
use BBS\Nova\Translation\Resources\Locale as BaseResource;

class Locale extends BaseResource
{
    /**
     * {@inheritdoc}
     */
    public static $group = StaticLabel::GROUP_ADMINISTRATION;
}
```

## GraphQL

@TODO...
