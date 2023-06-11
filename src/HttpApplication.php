<?php
namespace Lubed\HttpApplication;

use Lubed\Supports\{ServiceProvider};
use Lubed\Http\Response as HttpResponse;
use Lubed\Container\{DefaultContainer};
use Lubed\Utils\Config;

final class HttpApplication extends DefaultContainer
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

    public function getConfig():Config
    {
        return $this->config;
    }

    public function getRequest()
    {
        return $this->get('lubed_http_request');
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

        $request = $this->get('lubed_http_request');
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
        $response = new HttpResponse($body);
        $response->send();

        return $this->has_run;
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
