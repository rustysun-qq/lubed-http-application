<?php
namespace Lubed\HttpApplication;

// use Lubed\Logger\LoggerAware;
use Lubed\Http\Request;
use Lubed\Router\Router;
use Logger;
use Lubed\Container\Container;

//TODO:???
class DefaultDispatcher
{
    private $logger;
    private $interceptors=[];
    private $dispatcher_info;
    private $router;

    public function __construct(Container &$app)
    {
        $this->router = $app->get('lubed_router_router');
    }

    public function dispatch(Request &$request) {
        if(!$this->router){
            AppExceptions::dispatchFailed('NOT FOUND ROUTER',[
                'method'=>__METHOD__
            ]);
        }

        $method = $request->getMethod();
        $uri = $request->getUri();
        $path = $uri->getPath();
        $key = sprintf('%s %s',$method,$path);
        $callee = $this->router->routing($key);

        if (!$callee) {
           AppExceptions::dispatchFailed(sprintf('Routing Failed:INVALID PATH %s',$path),[
                'method'=>__METHOD__
            ]); 
        }
        $request->setRouted(true);
        return $callee;
    }
}
