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
php artisan vendor:publish --provider="BBSLab\NovaTranslation\NovaTranslationServiceProvider"
```

You need to run migrations and seeds Locales.

```bash
php artisan migrate
```

## Models setup

// @TODO... Explain 

 * `use Traits\Translatable`
 
 * `auto_synced_models` in config.php
 
 * Define `$nonTranslatable` attributes (attributes that will be overridden during in all translations during an entry update).
 
 * Define `$onCreateTranslatable` attributes (attributes that will be copied during translations entry creation).
 
 * If your using `michielkempen/nova-order-field` package you must override system in model with:
 
```php
/**
 * {@inheritdoc}
 */
public function buildSortQuery()
{
    return static::query()->locale();
}
```

## Configration

You can publish the default configuration by running the following command : 
```bash
php artisan vendor:publish --provider="BBSLab\NovaTranslation\NovaTranslationServiceProvider"
```

### Using Cookies
By default, the locale is stored in the session upon change, but if you need to access it before the session is started, you can instruct the package to save it in the cookies by enabling it in the config : 
```php
'use_cookies' => true,
```
The cookie will hold the same name defined in `locale_session_key`

💡 **NOTE:** The cookie will be encrypted by default, to have it excluded you can add it to your `EncryptCookies` middleware :
```php
class EncryptCookies extends Middleware
{
    public function __construct(EncrypterContract $encrypter)
    {
        parent::__construct($encrypter);

        $this->except = array_merge($this->except, [
            // ...
            NovaTranslation::localeSessionKey(),
        ]);
    }
}

```
## Config Nova

Add `SetLocale` middleware in application kernel.

```php
// app/Http/Kernel.php

protected $middleware = [
    // ...
    \BBSLab\NovaTranslation\Http\Middleware\SetLocale::class,
];
```

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

### Nova resource

Nova Resource MUST **extends** `BBSLab\NovaTranslation\Resources\TranslatableResource` to work.

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

### Directives `@allTranslations`, `@paginateTranslations`, `@firstTranslation` related

Acting as similar `@all`, `@paginate`, `@first`.

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

## Flags resources

Flags UTF-8 (e.g `en.json`) came from [EmojiTerra](https://emojiterra.com/flags/)

## TODO

- [ ] Add order button on keys heading
- [ ] Add search bar to filter keys
- [ ] Add checkboxes to enable/disable display of locale
- [ ] Add custom message/component when no locale is selected
