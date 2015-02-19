<?php


namespace Distilleries\DatatableBuilder\States;

use \View;

trait DatatableStateTrait {

    /**
     * @var \Distilleries\DatatableBuilder\EloquentDatatable $datatable
     * Injected by the constructor
     */
    protected $datatable;


    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------

    public function getIndexDatatable()
    {
        $this->datatable->build();
        $datatable = $this->datatable->generateHtmlRender();
        return view('datatable-builder::form.state.datatable')->with([
            'datatable' => $datatable
        ]);
    }

    // ------------------------------------------------------------------------------------------------

    public function getDatatable()
    {
        $this->datatable->setModel($this->model);
        $this->datatable->build();

        return $this->datatable->generateColomns();

    }
} 