<?php namespace Distilleries\DatatableBuilder;

use \Datatable;
use Illuminate\Database\Eloquent\Model;
use \ReflectionClass;
use \FormBuilder;
use \Request;

abstract class EloquentDatatable
{

    protected $model;
    protected $colomns;
    protected $form = null;
    protected $colomnsDisplay = [];
    protected $datatableOptions = [];

    // 0 can be an integer to represents the column's number or it can be a string that references the column's name
    protected $defaultOrder = [[0, 'desc']];

    // ------------------------------------------------------------------------------------------------

    public function __construct(Model $model = null)
    {
        $this->model = $model;
    }

    /**
     * @param Eloquent $model
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }


    // ------------------------------------------------------------------------------------------------

    /**
     * @param string $name
     * @param \Closure $closure
     */
    public function add($name, $closure = null, $translation = '')
    {
        if (!empty($closure)) {
            $this->colomns[] = [
                $name,
                $closure
            ];
        } else {
            $this->colomns[] = $name;
        }

        $this->addTranslation($name, $translation);

        return $this;

    }

    /**
     * @param string $translation
     * @param string $name
     */
    public function addTranslation($name, $translation)
    {
        $this->colomnsDisplay[] = (!empty($translation)) ? $translation : ucfirst($name);
    }

    // ------------------------------------------------------------------------------------------------

    public function applyFilters()
    {
        $allInput = Request::all();
        $columns  = \Schema::getColumnListing($this->model->getTable());

        foreach ($allInput as $name => $input) {
            if (in_array($name, $columns) && $input != '') {

                $this->model = $this->model->where($name, '=', $input);
            }
        }
    }

    // ------------------------------------------------------------------------------------------------

    public function generateColomns()
    {
        $this->applyFilters();

        $datatable        = Datatable::query($this->model);
        $colSearchAndSort = [];
        $sortOnly         = [];

        if (!empty($this->colomns)) {
            foreach ($this->colomns as $key => $value) {

                if (is_string($value)) {
                    $datatable->showColumns($value);
                    $colSearchAndSort[] = $value;

                } else {
                    if (is_array($value) && count($value) == 2) {
                        $datatable->addColumn($value[0], $value[1]);
                        $sortOnly[] = $value[0];
                    }
                }

            }
        }

        $datatable = $this->setClassRow($datatable);
        $datatable->orderColumns(array_merge($colSearchAndSort, $sortOnly));
        $datatable->searchColumns($colSearchAndSort);

        return $datatable->make();
    }

    public function setClassRow($datatable)
    {
        //DT_RowClass
        $datatable->setRowClass(function ($row) {
            return (isset($row->status) && empty($row->status)) ? 'danger' : '';
        });

        return $datatable;
    }

    // ------------------------------------------------------------------------------------------------
    public function generateHtmlRender($template = 'datatable-builder::part.datatable', $route = '')
    {
        return view($template, [
            'colomns_display'   => $this->colomnsDisplay,
            'datatable_options' => $this->addOptions(),
            'id'                => strtolower(str_replace('\\','_',get_class($this))),
            'route'             => !empty($route) ? $route : $this->getControllerNameForAction() . '@getDatatable',
            'filters'           => $this->addFilter(),
        ]);
    }

    // ------------------------------------------------------------------------------------------------
    public function addDefaultAction($template = 'datatable-builder::form.components.datatable.actions', $route = '')
    {

        $reflection = new ReflectionClass(get_class($this));

        $this->add('actions', function ($model) use ($template, $reflection, $route) {
            return view($template, [
                'data'  => $model->toArray(),
                'route' => !empty($route) ? $route . '@' : $this->getControllerNameForAction() . '@'
            ])->render();
        });
    }

    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------


    protected function addFilter($template = 'datatable-builder::form.components.datatable.filter')
    {
        $this->form = FormBuilder::plain();
        $this->filters();

        $filter_content = view($template, [
            'form' => $this->form
        ])->render();


        return $filter_content;

    }

    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------


    protected function addOptions()
    {
        if (!array_key_exists('order', $this->datatableOptions) && !empty($this->defaultOrder)) {
            if (is_array($this->defaultOrder)) {
                foreach ($this->defaultOrder as $keyOrder => $order) {
                    if (is_string($order[0])) {
                        foreach ($this->colomns as $key => $colomn) {
                            if (is_array($colomn)) {
                                $colomn = $colomn[0];
                            }
                            if ($colomn == $order[0]) {
                                $this->defaultOrder[$keyOrder][0] = $key;
                            }
                        }
                        if (is_string($this->defaultOrder[$keyOrder][0])) {
                            $this->defaultOrder[$keyOrder][0] = 0;
                        }
                    }
                }
                $this->datatableOptions['order'] = $this->defaultOrder;
            }
        }

        return $this->datatableOptions;
    }

    // ------------------------------------------------------------------------------------------------

    protected function getControllerNameForAction()
    {

        $action = explode('@', \Route::currentRouteAction());

        return '\\' . $action[0];
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