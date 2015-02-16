#Laravel 4  Datatable Builder


Based on [chumper/datatable](https://github.com/chumper/datatable).
It's an abstract class to implement the datatable like [the form generator](https://github.com/Distilleries/FormBuilder).



## Table of contents
1. [Installation](#installation)
2. [Basic usage](#basic-usage)
3. [Closure](#closure)
4. [Filters](#filters)
  


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
       'Distilleries\DatatableBuilder\DatatableBuilderServiceProvider',
    ]
```

And Facade (also in `config/app.php`)
   

``` php
    'aliases' => [
        // ...
        'Datatable'       => 'Distilleries\DatatableBuilder\Facades\DatatableBuilder',
    ]
```

## Basic usage

Creating form classes is easy. Lets assume PSR-4 is set for loading namespace `Project` in `app/Project` folder.
 With a simple artisan command I can create form:

``` sh
    php artisan datatablebuilder:make app/Project/Datatables/PostDatatable
```

you create form class in path `app/Project/Datatables/PostDatatable.php` that looks like this:

``` php
<?php namespace Project\Datatables;

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
php artisan datatablebuilder:make app/Project/Datatables/SongDatatable --fields="name, lyrics"
```

And that will create form in path `app/Project/Forms/SongForm.php` with content:

``` php
<?php namespace Project\Datatables;

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

`add($name, $closure = null, $translation = '')`

Param | Usage
----- | -----
name  | Name of the column
closure  | Function with the model in parameter to return a template. By default null use the attribute of the model.
translation | Translation of the column, by default empty use the column name


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
        return View::make('admin.address.html_row,[
        ])->render();
    });
```   


## Filters
You can use complex filter to filter your datatable.
To do that I use the library [FormBuilder](https://github.com/Distilleries/FormBuilder).
All the datatable have a plain form filter. If you had field on this forn that display the filters.

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