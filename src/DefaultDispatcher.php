<?php
namespace Lubed\HttpApplication;

// use Lubed\Logger\LoggerAware;
use Lubed\Reflections\ReflectionFactory;
use Lubed\Reflections\ReflectionFactoryAware;
use Lubed\Http\Request;
use Lubed\Router\Router;
use Logger;

//TODO:???
class DefaultDispatcher
{
    private $logger;
    protected $reflection_factory;
    private $interceptors=[];
    private $dispatcher_info;
    private $router;

    public function __construct(Router &$router)
    {
        $this->router = $router;
    }

    public function dispatch(HttpRequest &$request) {
        if(!$this->router){
            AppExceptions::dispatchFailed('NOT FOUND ROUTER',[
                'class'=>__CLASS__,'method'=>__METHOD__
            ]);
        }

        $method = $request->getMethod();
        $uri =$request->getUri();
        $path = $uri->getPath();
        $key = sprintf('%s %s',$method,$path);
        $callee=$this->router->routing($key);
        if (!$callee) {
           AppExceptions::dispatchFailed(sprintf('Routing Failed:INVALID PATH %s',$path),[
                'class'=>__CLASS__,'method'=>__METHOD__
            ]); 
        }
        $request->setRouted(true);
        return $callee;
    }

    public function setReflectionFactory(ReflectionFactory $reflection_factory) {
        $this->reflection_factory=$reflection_factory;
    }

    // public function setLogger(Logger $logger) {
    //     $this->logger=$logger;
    // }

    private function init($request)
    {
        $this->initRoute($request);
        //TODO:controller instance
        $instance=new $class_name($request, $response, $this->config);

        if ($instance instanceof Controller) {
            $instance->init();
        }

        //TODO:route
        $action=$routeInfo->getAction();

        if (!method_exists($instance, $action)) {
            throw new RustException(ErrorCode::METHOD_NOT_FOUND);
        }

        call_user_func_array([$instance, $action], []);
    }

    private function initRoute(&$request){
        if(false === method_exists($request,'routeRequest'))
        {
            return;
        }
        if (true === $request->isRouted()) {
            return;
        }
        $route = $request->routeRequest($request);
var_dump($route->getDestination());die("\ninit Route");
        $routeInfo=$request->getRouteInfo();

        if (!$routeInfo->getController()) {
            throw new HttpException(404);
        }

        $class_name = $routeInfo->getControllerClass();
    }
}
