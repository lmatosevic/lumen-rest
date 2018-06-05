# Lumen REST
> Lumen package for creating simple REST APIs.

## Installation
Install the package using composer by executing following command:
```sh
composer require lujo5/lumen-rest
```
Or you can add in `composer.json` the followign line:
```sh
"require": {
    ...
    "lujo5/lumen-rest": "*"
}
```
And then run: 
```sh
composer install
```

## Description
This Lumen package consists of two classes: `RestRoute` and `RestController`.
- `RestRoute` does all the routing operations in method `route(...)` for specific resource.
- `RestController` should be extended by some other controller class for specific resource.

## Usage

### 1. Create Eloquent model
In order to use this REST package, first you must create eloquent model for some resource.

Example Article model:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model {

	protected $table = 'article';

    protected $fillable = [
        'title',
        'description',
        'content'
    ];

    // Optional realtions and other Eloquent stuff
    public function articleAuthor() {
        return $this->hasOne('App\Models\Author');
    }
}
```

### 2. Create Controller
After model is created, you can create simple Controller class and EXTEND the RestController class from this package.
Also, you must implement method `getModel()` which must return Eloquent model created in last step which is the
resource you want to expose through this REST API.

Example Controller class:
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use App\Models\Article;
use Lujo5\Lumen\Rest\RestController;


class ArticleController extends RestController {
    /**
     * @return Model
     */
    protected function getModel() {
        return new Article();
    }
    
    // Optional override, transforms every model before returning it from API.
    protected function beforeGet($model) {
        return $model;
    }
    
    // Optional override, transform received request before creating new model from it.
    protected function beforeCreate($request) {
        return $request->all();
    }
    
    // Optional override, transform received request before updating existing model from it.
    protected function beforeUpdate($request) {
        return $request->all();
    }
    
    // Optional override, perform some action on/with model before it is deleted.
    protected function beforeDelete($model) {
        return null;
    }
}
```

All optional override methods are not required if you are not using them, they can be safely left out from
your controller class implementation.

### 3. Create routing
After previouse two steps are finished, open your Lumen routes in `routes/web.php`, and create routing structure
using `RestRoute` class static method `route($router, $prefix, $controller, $include = null)`.

Example `routes/web.php` rotuing:
```php
<?php 

use Laravel\Lumen\Routing\Router;
use Lujo5\Lumen\Rest\RestRoute;

/**
 * @var $router Router
 */

// This will create all article routes ('INDEX', 'ONE', 'CREATE', 'UPDATE', 'DELETE') for routes /articles/*
RestRoute::route($router, 'articles', 'ArticleController');

// This will create only 'INDEX' and 'ONE' for routes /users/*
RestRoute::route($router, 'users', 'UserController', ['INDEX', 'ONE']);

// Example subgroup of routes (/article/authors/*)
$router->group(['prefix' => 'article'], function ($subRoute) {
        RestRoute::route($subRoute, 'authors', 'AuthorController');
    }
);
```

Generated REST resource routes are in the following format:

| Method | Path           | Function | Description          |
| ------ | -------------- | ---------|--------------------- |
| GET    | /resource      | Index    | Get all resources*   |
| GET    | /resource/{id} | One      | Get one resource     |
| POST   | /resource      | Create   | Create new resource  |
| PUT    | /resource/{id} | Update   | Update resource      |
| DELETE | /resource/{id} | Delete   | Delete resource      |

\* _Get all resources_ has following HTTP GET URL parameters:
* **skip** - How many resources to skip (e.g. 30)
* **limit** - How many resources to retreive (e.g. 15)
* **sort** - Filed on which to sort returned resources (e.g. 'first_name')
* **order** - Ordering of returend resources ('asc' or 'desc')

LICENSE
---
MIT