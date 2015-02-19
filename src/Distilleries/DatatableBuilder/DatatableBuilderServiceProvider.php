<?php namespace Distilleries\DatatableBuilder;

use Chumper\Datatable\Datatable;
use Illuminate\Support\ServiceProvider;

class DatatableBuilderServiceProvider extends ServiceProvider {


    protected $package = 'datatable-builder';

    public function boot()
    {

        $this->loadViewsFrom(__DIR__.'/../../views', $this->package);
        $this->publishes([
            __DIR__.'/../../config/config.php'    => config_path($this->package.'.php'),
            __DIR__.'/../../config/datatable.php' => config_path('chumper_datatable.php'),
        ]);
        $this->publishes([
            __DIR__.'/../../views' => base_path('resources/views/vendor/'.$this->package),
        ], 'views');

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php',
            $this->package
        );


        $this->app['datatable'] = $this->app->share(function ($app)
        {
            return new Datatable;
        });

        $this->registerCommands($this->app['file']);
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


    protected function registerCommands($filesystem)
    {
        $files = $filesystem->allFiles(__DIR__.'/Console/');

        foreach ($files as $file)
        {
            if (strpos($file->getPathName(), 'Lib') === false)
            {
                $this->commands('Distilleries\DatatableBuilder\Console\\'.preg_replace('/\.php/i', '', $file->getFilename()));
            }


        }
    }
}