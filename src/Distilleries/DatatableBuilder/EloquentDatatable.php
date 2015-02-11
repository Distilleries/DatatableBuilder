<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 11/02/2015
 * Time: 10:29 AM
 */

namespace Distilleries;


use \Datatable, \Eloquent, \View, \ReflectionClass, \FormBuilder, \Input;

abstract class EloquentDatatable {

    protected $model;
    protected $colomns;
    protected $form           = null;
    protected $colomnsDisplay = [];

    // ------------------------------------------------------------------------------------------------

    public function __construct(Eloquent $model = null)
    {
        $this->model = $model;
    }

    /**
     * @param Eloquent $model
     */
    public function setModel(Eloquent $model)
    {
        $this->model = $model;
    }


    // ------------------------------------------------------------------------------------------------

    public function add($name, $closure = null, $translation = '')
    {
        if (!empty($closure))
        {
            $this->colomns[] = [
                $name,
                $closure
            ];
        } else
        {
            $this->colomns[] = $name;
        }

        $this->addTranslation($name, $translation);

        return $this;

    }

    public function addTranslation($name, $translation)
    {
        $this->colomnsDisplay[] = (!empty($translation)) ? $translation : ucfirst($name);
    }

    // ------------------------------------------------------------------------------------------------

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

    // ------------------------------------------------------------------------------------------------

    public function generateColomns()
    {
        $this->applyFilters();

        $datatable        = Datatable::query($this->model);
        $colSearchAndSort = array();

        if (!empty($this->colomns))
        {
            foreach ($this->colomns as $key => $value)
            {

                if (is_string($value))
                {
                    $datatable->showColumns($value);
                    $colSearchAndSort[] = $value;

                } else if (is_array($value) && count($value) == 2)
                {
                    $datatable->addColumn($value[0], $value[1]);
                }

            }
        }

        $datatable = $this->setClassRow($datatable);
        $datatable->orderColumns($colSearchAndSort);
        $datatable->searchColumns($colSearchAndSort);

        return $datatable->make();

    }

    public function setClassRow($datatable)
    {
        //DT_RowClass
        $datatable->setRowClass(function ($row)
        {
            return (isset($row->status) and empty($row->status)) ? 'danger' : '';
        });

        return $datatable;
    }

    // ------------------------------------------------------------------------------------------------
    public function generateHtmlRender($template = 'datatable-builder::part.datatable', $route = '')
    {


        $action = explode('@', \Route::currentRouteAction());

        return View::make($template, [
            'colomns_display' => $this->colomnsDisplay,
            'route'           => !empty($route) ? $route : $action[0] . '@getDatatable',
            'filters'         => $this->addFilter(),
        ]);
    }

    // ------------------------------------------------------------------------------------------------
    public function addDefaultAction($template = 'datatable-builder::form.components.datatable.actions', $route = '')
    {

        $reflection = new ReflectionClass(get_class($this));

        $this->add('actions', function ($model) use ($template, $reflection)
        {
            $action = explode('@', \Route::currentRouteAction());

            return View::make($template, array(
                'data'  => $model->toArray(),
                'route' => !empty($route) ? $route . '@' : reset($action) . '@'
            ))->render();
        });
    }

    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------


    protected function addFilter($template='datatable-builder::form.components.datatable.filter')
    {
        $this->form = FormBuilder::plain();
        $this->filters();

        $filter_content = View::make($template, [
            'form' => $this->form
        ])->render();


        return $filter_content;

    }

    // ------------------------------------------------------------------------------------------------

    public function filters()
    {

        //Add the fileds of the filter form here

    }

    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------

    abstract public function build();

}