<?php namespace Distilleries\DatatableBuilder;

use Chumper\Datatable\Datatable;
use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;

class DatatableBuilderServiceProvider extends ServiceProvider {


    protected $package = 'datatable-builder';

    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../views', $this->package);
        $this->loadTranslationsFrom(__DIR__.'/../../lang', $this->package);
        $this->publishes([
            __DIR__.'/../../config/config.php'    => config_path($this->package.'.php'),
            __DIR__.'/../../config/datatable.php' => config_path('chumper_datatable.php'),
        ]);

        $this->publishes([
            __DIR__.'/../../views'        => base_path('resources/views/vendor/'.$this->package),
        ], 'views');

        $this->publishes([
            __DIR__.'/../../resources/assets' => base_path('resources/assets/vendor/'.$this->package),
        ], 'assets');

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


        $this->app->singleton('datatable',function()
        {
            return new Datatable;
        });

        $this->alias();
        $this->registerCommands(new Filesystem);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return array('datatable');
    }


    /**
     * @param Filesystem $filesystem
     */
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

    public function alias() {

        AliasLoader::getInstance()->alias(
            'View',
            'Illuminate\Support\Facades\View'
        );
        AliasLoader::getInstance()->alias(
            'FormBuilder',
            'Distilleries\FormBuilder\Facades\FormBuilder'
        );
        AliasLoader::getInstance()->alias(
            'Input',
            'Illuminate\Support\Facades\Input'
        );
        AliasLoader::getInstance()->alias(
            'Schema',
            'Illuminate\Support\Facades\Schema'
        );
        AliasLoader::getInstance()->alias(
            'Route',
            'Illuminate\Support\Facades\Route'
        );
    }
}