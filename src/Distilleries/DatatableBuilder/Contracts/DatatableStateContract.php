<?php

namespace Distilleries\DatatableBuilder\Contracts;

interface DatatableStateContract
{
    /**
     * View to display datatable.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndexDatatable();

    /**
     * Return generated datatable.
     *
     * @return mixed
     */
    public function getDatatable();
}
