<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 11/02/2015
 * Time: 10:24 AM
 */

namespace Distilleries\DatatableBuilder\Facades;

use Illuminate\Support\Facades\Facade;

class DatatableBuilder extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'datatable'; }

}