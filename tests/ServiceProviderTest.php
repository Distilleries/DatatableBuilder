<?php namespace Orchestra\Testbench\TestCase;
/**
 * Created by PhpStorm.
 * User: cross
 * Date: 2/26/2015
 * Time: 3:35 PM
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use \Mockery as m;

class ServiceProviderTest extends \Orchestra\Testbench\TestCase
{
    protected $datatableFile;

    public function setUp(){

        parent::setUp();

        $this->app['Illuminate\Contracts\Console\Kernel']->call('vendor:publish');

        $this->refreshApplication();
        $this->datatableFile = $this->app['path'].'/TestDatatable.php';

        Schema::create('users', function(Blueprint $table)
        {
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });

        User::create(['name' => 'John', 'email' => 'email@test']);


    }
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', 'Orchestra\Testbench\TestCase\Kernel');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', array(
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ));

        $app['router']->controller('datatable', 'Orchestra\Testbench\TestCase\DatatableController');
        $app['router']->controller('filters', 'Orchestra\Testbench\TestCase\FilterController');

    }

    protected function getPackageProviders()
    {
        return [
            'Distilleries\FormBuilder\FormBuilderServiceProvider',
            'Distilleries\DatatableBuilder\DatatableBuilderServiceProvider'
        ];
    }

    protected function getPackageAliases()
    {
        return [
            'FormBuilder'   => 'Distilleries\FormBuilder\Facades\FormBuilder',
            'Datatable'     => 'Distilleries\DatatableBuilder\Facades\DatatableBuilder',
        ];
    }

    public function testService()
    {
        $service = $this->app->getProvider('Distilleries\DatatableBuilder\DatatableBuilderServiceProvider');
        $facades = $service->provides();
        $this->assertTrue([ 'datatable' ] == $facades);

        $service->boot();
        $service->register();
    }

    public function testArtisanCreateDatatable()
    {
        $this->app['Illuminate\Contracts\Console\Kernel']->call('datatable:make', [
            'name' => 'TestDatatable',
            '--fields' => 'test1, test2',
        ]);
        require_once($this->app['path'].'/TestDatatable.php');

        $this->assertFileExists($this->datatableFile);
    }

    public function testDatatable()
    {
        $this->call('GET', 'datatable');
        $this->assertResponseOk();
    }

    public function testFilters()
    {
        $view = $this->call('GET', 'filters?name=John');
        $this->assertViewHas('email', "email@test");
        //$this->assertResponseOk();
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();

        //File::delete($this->datatableFile);
    }
}


use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Distilleries\DatatableBuilder\States\DatatableStateTrait;
use Distilleries\DatatableBuilder\EloquentDatatable;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

    use Authenticatable, CanResetPassword;

    protected $table = 'users';

    protected $fillable = ['name', 'email'];

}


class DatatableController extends \Illuminate\Routing\Controller {

    use DatatableStateTrait;

    public function __construct(User $model, \App\TestDatatable $datatable)
    {
        $this->model = $model;
        $this->datatable = $datatable;
    }

    public function getIndex()
    {
        return view('welcome',[
            'datatable' => $this->getIndexDatatable()
        ]);
    }
}
class FilterController extends \Illuminate\Routing\Controller {

    use DatatableStateTrait;

    public function __construct(User $model, TestFilters $datatable)
    {
        $this->model = $model;
        $this->datatable = $datatable;

        $this->datatable->setModel($model);


    }

    public function getIndex()
    {
        return view('welcome',[
            'datatable' => $this->getIndexDatatable(),
            'email' => $this->getDatatable()->getData()->aaData[0]->{'1'}
        ]);
    }
}

class TestFilters extends EloquentDatatable
{
    public function build()
    {
        $this
            ->add('name', null, 'name')
            ->add('email' ,null, 'email');

        $this->addDefaultAction();

    }

    public function filters()
    {
        $this->form->add('name', 'text', [
            'name'     => 'name'
        ]);

    }

    public function applyFilters()
    {
        parent::applyFilters();
        //$this->model = $this->model->where('name', '=', 'John');
    }

    public function setClassRow($datatable)
    {
        $datatable->setRowClass(function ($row)
        {
            return (isset($row->status) and empty($row->status)) ? 'danger' : '';
        });

        return $datatable;
    }
}

use Orchestra\Testbench\Http\Kernel as BaseKernel;
class Kernel extends BaseKernel
{

    protected $bootstrappers = [];

    protected $middleware = [
        'Illuminate\Cookie\Middleware\EncryptCookies',
        'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
        'Illuminate\Session\Middleware\StartSession',
        'Illuminate\View\Middleware\ShareErrorsFromSession',
    ];
}