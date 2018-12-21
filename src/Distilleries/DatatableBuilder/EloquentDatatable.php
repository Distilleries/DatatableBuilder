<?php

namespace Distilleries\DatatableBuilder;

use Route;
use Schema;
use Request;
use Datatable;
use FormBuilder;
use ReflectionClass;
use Illuminate\Database\Eloquent\Model;

abstract class EloquentDatatable
{
    /**
     * Eloquent model.
     * 
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Eloquent model underlying table.
     * 
     * @var string
     */
    protected $table = '';
    
    /**
     * Datatable columns.
     * 
     * @var array
     */
    protected $colomns;
    
    /**
     * Form implementation.
     *
     * @var \Kris\LaravelFormBuilder\Form|null
     */
    protected $form = null;

    /**
     * Datatable columns to display.
     *
     * @var array
     */
    protected $colomnsDisplay = [];

    /**
     * Datatable columns orderable state.
     *
     * @var array
     */
    protected $columnsOrderable = [];

    /**
     * Datatable extra options.
     *
     * @var array
     */
    protected $datatableOptions = [];

    /**
     * Datatable order data (0 can be an integer to represents the column's number or it can be a string that references the column's name).
     *
     * @var array
     */
    protected $defaultOrder = [[0, 'desc']];

    /**
     * EloquentDatatable constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model|null $model
     */
    public function __construct(Model $model = null)
    {
        if (!empty($model)) {
            $this->setModel($model);
        }
    }

    /**
     * Model setter.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
        $this->table = $this->model->getTable();
    }

    /**
     * Add column to datatable.
     *
     * @param string $name
     * @param \Closure|null $closure
     * @param string|\Symfony\Component\Translation\TranslatorInterface $translation
     * @param bool $orderable
     * @return $this
     */
    public function add($name, $closure = null, $translation = '', $orderable = true)
    {
        if (! empty($closure)) {
            $this->colomns[] = [
                $name,
                $closure,
            ];
        } else {
            $this->colomns[] = $name;
        }

        $this->addTranslation($name, $translation);
        $this->addOrderable($name, $orderable);

        return $this;
    }

    /**
     * Add translation column.
     *
     * @param string $name
     * @param string|\Symfony\Component\Translation\TranslatorInterface $translation
     * @return void
     */
    public function addTranslation($name, $translation)
    {
        $this->colomnsDisplay[] = ! empty($translation) ? $translation : ucfirst($name);
    }

    /**
     * Add orderable column.
     *
     * @param string $name
     * @param bool $translation
     * @return void
     */
    public function addOrderable($name, $orderable)
    {
        $this->columnsOrderable[$name] = $orderable;
    }

    /**
     * Generate specified columns for current datatable.
     *
     * @return mixed
     */
    public function generateColomns()
    {
        $this->model = $this->baseQuery();

        $this->applyFilters();

        $datatable = Datatable::query($this->model);
        $colSearchAndSort = [];
        $sortOnly = [];

        if (! empty($this->colomns)) {
            foreach ($this->colomns as $key => $value) {

                if (is_string($value)) {
                    $datatable->showColumns($value);
                    $colSearchAndSort[] = $value;
                } else {
                    if (is_array($value) && (count($value) === 2)) {
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

    /**
     * Return Datatable base query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function baseQuery()
    {
        return $this->model->query();
    }

    /**
     * Set DT_RowClass for given datatable.
     *
     * @param \Chumper\Datatable\Engines\QueryEngine $datatable
     * @return \Chumper\Datatable\Engines\QueryEngine
     */
    public function setClassRow($datatable)
    {
        $datatable->setRowClass(function ($row) {
            return (isset($row->status) && empty($row->status)) ? 'danger' : '';
        });

        return $datatable;
    }

    /**
     * Generate rendered HTML view for current datatable.
     *
     * @param string $template
     * @param string $route
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function generateHtmlRender($template = 'datatable-builder::part.datatable', $route = '')
    {
        return view($template, [
            'colomns_display' => $this->colomnsDisplay,
            'datatable_options' => $this->addOptions(),
            'id' => strtolower(str_replace('\\', '_', get_class($this))),
            'route' => ! empty($route) ? $route : $this->getControllerNameForAction() . '@getDatatable',
            'filters' => $this->addFilter(),
        ]);
    }

    /**
     * Add default actions to datatable.
     *
     * @param string $template
     * @param string $route
     * @return void
     */
    public function addDefaultAction($template = 'datatable-builder::form.components.datatable.actions', $route = '')
    {
        $reflection = new ReflectionClass(get_class($this));

        $this->add('actions', function ($model) use ($template, $reflection, $route) {
            return view($template, [
                'data' => $model->toArray(),
                'route' => ! empty($route) ? $route . '@' : $this->getControllerNameForAction() . '@',
            ])->render();
        }, 'Actions', false);
    }

    /**
     * Add filters form to current datatable.
     *
     * @param string $template
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    protected function addFilter($template = 'datatable-builder::form.components.datatable.filter')
    {
        $this->form = FormBuilder::plain();
        $this->filters();

        $filter_content = view($template, [
            'form' => $this->form,
        ])->render();

        return $filter_content;
    }

    /**
     * Add default options to current datatable.
     *
     * @return array
     */
    protected function addOptions()
    {
        if (! array_key_exists('order', $this->datatableOptions) && ! empty($this->defaultOrder)) {
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

        $nonOrderableColumns = [];
        foreach (array_values($this->columnsOrderable) as $index => $orderableState) {
            if (!$orderableState) {
                $nonOrderableColumns[] = $index;
            }
        }
        if (!empty($nonOrderableColumns)) {
            $this->datatableOptions['columnDefs'] = [
                ['orderable' => false, 'targets' => $nonOrderableColumns],
            ];
        }

        return $this->datatableOptions;
    }

    /**
     * Get controller name for action based on current route.
     *
     * @return string
     */
    protected function getControllerNameForAction()
    {
        $action = explode('@', Route::currentRouteAction());

        return '\\' . $action[0];
    }

    /**
     * Add the fields filters form here.
     *
     * @return void
     */
    public function filters()
    {
        //
    }

    /**
     * Apply filters by default on each fields of setted model.
     *
     * @return void
     */
    public function applyFilters()
    {
        $columns = Schema::getColumnListing($this->table);

        $allInput = Request::all();
        foreach ($allInput as $name => $input) {
            if (in_array($name, $columns) && ($input != '')) {
                $this->model = $this->model->where($this->table . '.' . $name, '=', $input);
            }
        }
    }

    /**
     * Compile all added columns and build datatable.
     *
     * @return void
     */
    abstract public function build();
}