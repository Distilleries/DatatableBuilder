<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 11/02/2015
 * Time: 10:19 AM
 */

namespace Distilleries\DatatableBuilder;
use Chumper\Datatable\Datatable;
use \File;
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

        $this->registerCommands();
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


    protected function registerCommands()
    {
        $files = File::allFiles(__DIR__ . '/Console/');

        foreach ($files as $file)
        {
            if (strpos($file->getPathName(), 'Lib') === false)
            {
                $this->commands('Distilleries\DatatableBuilder\Console\\' . preg_replace('/\.php/i', '', $file->getFilename()));
            }


        }
    }
}