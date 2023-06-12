<?php
namespace Lubed\HttpApplication;

use Lubed\Data\Connection;
use Lubed\Utils\Registry;
use Lubed\Supports\{ServiceProvider};
use Lubed\Http\Response as HttpResponse;
use Lubed\Container\{DefaultContainer};
use Lubed\Utils\Config;
use Lubed\Data\DefaultDataSource;
use Lubed\Http\Request;

class HttpApplication extends DefaultContainer
{   private $name;
    private $config;
    private $request;
    private $has_run;
    private $methods;
    private $kernel;
    //boot providers
    private $booted;
    private $providers;
    //router
    private $router;
    //database
    private $default_dsn;

    public function __construct(Config $config, string $name='lubed_http_application')
    {
        $this->methods = [];
        $this->config = $config;
        $this->name = $name;
        $this->bootContainer();
    }

    public function init()
    {
        $this->registerKernel();
        $this->registerRouter();
    }

    public function initRequest($request = null)
    {
        if (! $request) {
            $request = Request::createFromGlobal();
        }

        $this->instance(Request::class, $request);

        return $request;
    }

    public function getConfig():Config
    {
        return $this->config;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getRouter():Router
    {
        return $this->get('lubed_router_router');
    }

    public function getOptions()
    {
        return $this->request_options;
    }

    public function getKernel()
    {
        return $this->get('kernel');
    }

    public function register($provider)
    {
        if (! $provider instanceof ServiceProvider) {
            $provider = new $provider($this);
        }

        if (array_key_exists($name = get_class($provider), $this->providers)) {
            return;
        }

        $this->providers[$name] = $provider;

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        if ($this->booted) {
            $this->bootProvider($provider);
        }
    }

    public function run()
    {
        if (!$this->has_run) {
            $this->has_run = true;
        }

        $request = $this->initRequest();
        $this->request = $request;
        $dispatcher = new DefaultDispatcher($this);
        $rdi = $dispatcher->dispatch($request);//dispatch to router
        $callee=[];
        if($rdi){
            $callee=[$rdi->getController(),$rdi->getAction()];
        }

        $this->getKernel()->setRequest($request);
        $this->getKernel()->init($callee);
        $body = NULL;
        $this->getKernel()->boot($body);
        if(!$body instanceof HttpResponse){
            $response = new HttpResponse($body);
            $response->send();
        }else{
            $body->send();
        }

        return $this->has_run;
    }
    //with database

    public function withDatabase() {
        $config = $this->getConfig();
        $ds_config=$config->get('data_sources');
        if($ds_config) {
            $ds=new DefaultDataSource($ds_config);
            $this->default_dsn = $ds_config->get('default');
            $this->alias(Lubed\Data\DefaultDataSource::class,'data_source');
            $this->instance('data_source',$ds);
            $registry = Registry::getInstance();
            $registry->set('conn',  $ds->getConnection($this->default_dsn));
        }
        return $this;
    }

    private function boot()
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            $this->bootProvider($provider);
        }

        $this->booted = true;
    }

    private function bootProvider($provider)
    {
        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }
    }

    private function bootContainer()
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance(self::class, $this);
        $this->registerContainerAliases();
    }

    private function registerContainerAliases()
    {
        $this->aliases = [
            'lubed_http_request' => \Lubed\Http\Request::class,
            'lubed_router_router'=> \Lubed\Router\Router::class
        ];
    }

    private function registerKernel(){
        $kernel_config = $this->config->get('kernel');
        if(!$kernel_config){
            AppExceptions::startFailed('http application kernel config not found',[
                'method'=>''.__METHOD__
            ]);
        }
        $starter_config = $kernel_config->get('starter');
        $starter_class = $starter_config->get('class');
        if(!$starter_class||false === class_exists($starter_class)){
            AppExceptions::startFailed('http application kernel starter not found',[
                'method'=>''.__METHOD__
            ]);
        }
        $parameters = $starter_config->get('parameters');
        if(!$parameters){
            AppExceptions::startFailed('http application kernel starter parameter is invalid',[
                'method'=>''.__METHOD__
            ]);
        }
        $starter = new $starter_class($parameters,$this);
        $starter->start();
    }

    private function registerRouter()
    {
        $router_config = $this->config->get('router');
        if (NULL === $router_config) {
            return;
        }
        $parameters = $router_config->get('parameters');
        $starter_class = $router_config->get('class');
        $instance = null;
        if ($starter_class && false !== class_exists($starter_class)) {
            $instance = new $starter_class($parameters, $this);
        }
        if (null !== $instance) {
            $instance->start();
        }
    }
}
