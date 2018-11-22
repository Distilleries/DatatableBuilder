[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Distilleries/DatatableBuilder/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Distilleries/DatatableBuilder/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Distilleries/DatatableBuilder/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Distilleries/DatatableBuilder/?branch=master)
[![Build Status](https://travis-ci.org/Distilleries/DatatableBuilder.svg?branch=master)](https://travis-ci.org/Distilleries/DatatableBuilder)
[![Total Downloads](https://poser.pugx.org/distilleries/datatable-builder/downloads)](https://packagist.org/packages/distilleries/datatable-builder)
[![Latest Stable Version](https://poser.pugx.org/distilleries/datatable-builder/version)](https://packagist.org/packages/distilleries/datatable-builder)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE)



#Laravel 5  Datatable Builder


Based on [chumper/datatable](https://github.com/chumper/datatable).
It's an abstract class to implement the datatable like [the form generator](https://github.com/Distilleries/FormBuilder).



## Table of contents
1. [Installation](#installation)
2. [Basic usage](#basic-usage)
3. [Closure](#closure)
4. [BaseQuery](#base-query)
5. [Filters](#filters)
6. [Styles](#styles)
7. [Controller](#controller)
  


##Installation
  
Add on your composer.json

``` json
    "require": {
        "distilleries/datatable-builder": "1.*",
    }
```

run `composer update`.

Add Service provider to `config/app.php`:

``` php
    'providers' => [
        // ...
	   'Distilleries\FormBuilder\FormBuilderServiceProvider',
       'Distilleries\DatatableBuilder\DatatableBuilderServiceProvider',
    ]
```

And Facade (also in `config/app.php`)
   

``` php
    'aliases' => [
        // ...
		'FormBuilder'     => 'Distilleries\FormBuilder\Facades\FormBuilder',
        'Datatable'       => 'Distilleries\DatatableBuilder\Facades\DatatableBuilder',
    ]
```

Add the javascript on your bower dependencies:
``` json
    "dependencies": {
        "datatables": "~1.10.4",
    }
```


Export the configuration:

```ssh
php artisan vendor:publish --provider="Distilleries\DatatableBuilder\DatatableBuilderServiceProvider"
```

Export the views  (optional):

```ssh
php artisan vendor:publish --provider="Distilleries\DatatableBuilder\DatatableBuilderServiceProvider"  --tag="views"
```

Export the Javascript assets  (optional):

Include the javascript with gulp or grunt `vendor/distilleries/datatable-builder/resources/**/*`.
If you don't use a task manager or you want override the javascript just publish those with the command line:

```ssh
php artisan vendor:publish --provider="Distilleries\DatatableBuilder\DatatableBuilderServiceProvider"  --tag="assets"
```
You can find those in `resources/assets/vendor/datatable-builder`.


## Basic usage

Creating form classes is easy.
With a simple artisan command I can create a datatable:

``` sh
php artisan datatable:make Datatables/PostDatatable
```

you create form class in path `app/Datatables/PostDatatable.php` that looks like this:

``` php
<?php namespace App\Datatables;

use Distilleries\DatatableBuilder\EloquentDatatable;

class PostDatatable extends EloquentDatatable
{
    public function build()
    {
        // Add fields here...

        $this->addDefaultAction();

    }
}
```

You can add fields which you want when creating command like this:


``` sh
php artisan datatable:make Datatables/SongDatatable --fields="name, lyrics"
```

And that will create a datatable in path `app/Datatables/SongDatatable.php` with content:

``` php
<?php namespace App\Datatables;

use Distilleries\DatatableBuilder\EloquentDatatable;

class SongDatatable extends EloquentDatatable
{
    public function build()
    {
        $this
            ->add('name',null,_('Name'))
            ->add('lyrics',null,_('Lyrics'));

        $this->addDefaultAction();

    }
}
```

The method `add` have in param:

`add($name, $closure = null, $translation = '', $orderable = true)`

Param | Usage
----- | -----
name  | Name of the column
closure  | Function with the model in parameter to return a template. By default null use the attribute of the model.
translation | Translation of the column, by default empty use the column name
orderable | Flag to handle column orderable state, by default true


## Closure
When you create a datatatable sometime the content need to be get from other table or stylize by a template like the actions buttons.

For example an address is link to a profile. To display the profile name on the datatable like the picture you can use the closure.

``` php
    $this->add('profile_name', function ($model)
    {
        $profile = $model->profile;
   
        return (empty($profile)?'':$profile->first_name . ' ' . $profile->last_name);
    }, _('Profile'));
    
 ```           
 
 On my model `Address` I have:

``` php
    public function profile()
    {
         return $this->belongsTo('Profile');
    }
```   
             
![datatable](http://distilleri.es/markdown/formbuilder/_images/datatable.png)


You can return a template if you want:

``` php
    $this->add('html_row', function ($model)
    {
        return View::make('admin.address.html_row',[
        ])->render();
    });
```   

## BaseQuery

You can override the base query for the datatable query.

By default it will send a fresh full Query: `$this->model->newModelQuery();`

``` php
    /**
     * {@inheritdoc}
     */
    protected function baseQuery()
    {
        return $this->model->newModelQuery()
            ->selectRaw("id, data->>'$.title' AS title, data->>'$.chapo' AS intro, created_at");
    }
```

## Filters
You can use complex filter to filter your datatable.
To do that I use the library [FormBuilder](https://github.com/Distilleries/FormBuilder).
All the datatable have a plain form filter. If you had field on this form that display the filters.

For example we want all the user only online.

I create a choice field:

``` php
public function filters()
{
    $this->form->add('status', 'choice', [
        'choices'     => StaticLabel::status(),
        'empty_value' => _('-'),
        'validation'  => 'required',
        'label'       => _('Status')
    ]);

}
```


![filters](http://distilleri.es/markdown/formbuilder/_images/filters.png)


When the filters are submitted you can apply it with the method `applyFilters`.
By default this method create a where with the name of the filter field if it's an attribute of the model.
If you want change this behaviour you can override it.

``` php
    public function applyFilters()
    {
        $allInput = Input::all();
        $columns  = \Schema::getColumnListing($this->model->getTable());

        foreach ($allInput as $name => $input)
        {
            if (in_array($name, $columns) and $input != '')
            {

                $this->model = $this->model->where($name, '=', $input);
            }
        }

    }
```

If you don't want create a global scope of your model you just want restrict the display for this datatable.
you can use `applyFilters` to do that.

For example I want display only the customer of the application:
The datable work on the user model.

``` php
    public function applyFilters()
    {
        parent::applyFilters();
        $customer = \Role::where('initials', '==', '@c')->get()->last();
        $this->model = $this->model->where('role_id', '=', $customer->id);
    }
```

## Styles
You want stylize your row to put it on green, blue or red.
You can do that with `setClassRow`. By default this method check the status attribute.
If the status exist and it is empty we add the danger class to display it in red.


``` php
    public function setClassRow($datatable)
    {
        $datatable->setRowClass(function ($row)
        {
            return (isset($row->status) and empty($row->status)) ? 'danger' : '';
        });

        return $datatable;
    }
```

##Controller
You can use the trait `Distilleries\DatatableBuilder\States\DatatableStateTrait` to add in your controller the default methods use with the datatable.

Example:
I created a controller `app/Http/Controllers/DatatableController`:

```php
<?php namespace App\Http\Controllers;

use App\Datatables\UserDatatable;

class DatatableController extends Controller {

	use \Distilleries\DatatableBuilder\States\DatatableStateTrait;
	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(\App\User $model, UserDatatable $datatable)
	{
		$this->model = $model;
		$this->datatable = $datatable;
	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return view('welcome',[
			'datatable'=>$this->getIndexDatatable()
		]);
	}

}
```

I add the controller on the route file :

```php
Route::controllers([
	'datatable' => 'DatatableController'
]);
```

Like you can see I inject the model and the datatable on the constructor.
On the welcome template I put:

```php
<html>
	<head>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
		<script src="//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js"></script>
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">

		<!-- Optional theme -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="//cdn.datatables.net/1.10.5/css/jquery.dataTables.min.css">

		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
		<script src="/vendor/datatable-builder/js/datatable.js"></script>
	</head>
	<body>
		<div class="container">
			{!! $datatable !!}
		</div>
	</body>
</html>
```
That it you have your datatable from the user model.