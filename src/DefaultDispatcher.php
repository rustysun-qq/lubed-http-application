<?php
namespace Lubed\HttpApplication;

use Lubed\Http\Request;
use Lubed\Router\Router;
use Lubed\Router\Routing\RoutingDestination;
use Lubed\Utils\Registry;

class DefaultDispatcher {
    private Router $router;

    public function __construct() {
        $registry=Registry::getInstance();
        $this->router=$registry->get('lubed_router_router');
        if (!$this->router) {
            AppExceptions::dispatchFailed('NOT FOUND ROUTER', [
                'method'=>__METHOD__
            ]);
        }
    }

    public function dispatch(Request &$request):?RoutingDestination {
        $method=$request->getMethod();
        $uri=$request->getUri();
        $path=$uri->getPath();
        $key=sprintf('%s %s', $method, $path);
        $destination=$this->router->routing($method,$path);
        if (!$destination) {
            AppExceptions::dispatchFailed(sprintf('Routing Failed:INVALID PATH %s', $path), [
                'method'=>__METHOD__
            ]);
        }
        $request->setRouted(true);
        return $destination;
    }
}
