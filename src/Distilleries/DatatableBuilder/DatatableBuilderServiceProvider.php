<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 11/02/2015
 * Time: 10:19 AM
 */

namespace Distilleries\DatatableBuilder;
use Chumper\Datatable\Datatable;
use Illuminate\Support\ServiceProvider;

class DatatableBuilderServiceProvider extends ServiceProvider {


    public function boot()
    {
        $this->package('distilleries/datatable-builder');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['datatable'] = $this->app->share(function($app)
        {
            return new Datatable;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('datatable');
    }
}