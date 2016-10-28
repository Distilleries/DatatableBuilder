<?php

namespace Distilleries\DatatableBuilder\States;

trait DatatableStateTrait
{
    /**
     * Datatable (injected by the constructor).
     *
     * @var \Distilleries\DatatableBuilder\EloquentDatatable $datatable
     */
    protected $datatable;

    /**
     * Model (injected by the contructor).
     *
     * @var \Illuminate\Database\Eloquent\Model $model
     */
    protected $model;

    /**
     * View to display datatable.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndexDatatable()
    {
        $this->datatable->build();
        $datatable = $this->datatable->generateHtmlRender();

        return view('datatable-builder::form.state.datatable')->with([
            'datatable' => $datatable,
        ]);
    }

    /**
     * Return generated datatable.
     *
     * @return mixed
     */
    public function getDatatable()
    {
        $this->datatable->setModel($this->model);
        $this->datatable->build();

        return $this->datatable->generateColomns();
    }
}
