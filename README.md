# Fractal Transformer generator for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lasselehtinen/cybertron.svg?style=flat-square)](https://packagist.org/packages/lasselehtinen/cybertron)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://travis-ci.org/lasselehtinen/cybertron.svg?branch=master)](https://travis-ci.org/lasselehtinen/cybertron)
[![Quality Score](https://img.shields.io/scrutinizer/g/lasselehtinen/cybertron.svg?style=flat-square)](https://scrutinizer-ci.com/g/lasselehtinen/cybertron)
[![StyleCI](https://styleci.io/repos/43743138/shield?branch=master)](https://styleci.io/repos/43743138)
[![Total Downloads](https://img.shields.io/packagist/dt/lasselehtinen/cybertron.svg?style=flat-square)](https://packagist.org/packages/lasselehtinen/cybertron)

The package provides an easy way to generate [Fractal Transformers](http://fractal.thephpleague.com/transformers/)
for your Laravel applications. The package automatically scans the models Eloquent relationships and attributes and generates the Transformer boiler plate. It also automatically adds casting for integer and boolean fields. 

## Example

### Model
```php
class TestModel extends Model
{
    /**
     * Example hasMany relationship
     */
    public function hasManySomethings()
    {
        return $this->hasMany(SomeOtherModel::class);
    }
}
```

### Migration
```php
    public function up()
    {
        Schema::create('test_models', function (Blueprint $table) {
            $table->increments('id_field');
            $table->boolean('boolean_field');
            $table->string('string_field');
        });
    }
```

### Result

```php
<?php

namespace App;

use League\Fractal;
use \lasselehtinen\Cybertron\Tests\TestModel;

class TestTransformer extends Fractal\TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var  array
     */
    protected $defaultIncludes = [
        'hasManySomethings',
    ];

    /**
     * Transform TestModel
     *
     * @param   TestModel $testModel
     * @return  array
     */
    public function transform(TestModel $testModel)
    {
        return [
            'id_field' => (integer) $testModel->id_field,
            'boolean_field' => (boolean) $testModel->boolean_field,
            'string_field' => $testModel->string_field,
        ];
    }

    /**
     * Include HasManySomethings
     *
     * @param  TestModel $testModel
     * @return \League\Fractal\Resource\Collection
     */
    public function includeHasManySomethings(TestModel $testModel)
    {
        return $this->collection($testModel->hasManySomethings, new SomeOtherModelTransformer);
    }

}

```

## Installation

You can pull in the package via composer:
``` bash
$ composer require lasselehtinen/cybertron --dev
```

Since you only need the generator for development, don't add the generator the providers array in `config/app.php`. Instead add it to `app/Providers/AppServiceProvider.php`as shown below:

```php
public function register()
{
    if ($this->app->environment() == 'local') {
        $this->app->register(lasselehtinen\Cybertron\CybertronServiceProvider::class);
    }
}
```

## Usage

Run the artisan command make:transformer and give the Transformers name and the model with the full namespace like so: 

```bash
php artisan make:transformer PostTransformer --model=\\App\\Post
```

Similar way as in Laravels built-in generators, you can provide a namespace for the generated Transformer.

```bash
php artisan make:transformer \\App\\Api\\V1\\Transformers\\PostTransformer --model=\\App\\Post
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.