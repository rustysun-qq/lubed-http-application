<?php
namespace Lubed\HttpApplication;

use Lubed\Http\Request;
use Lubed\Utils\Registry;

//TODO:???
class DefaultDispatcher {
    private $router;

    public function __construct() {
        $registry=Registry::getInstance();
        $this->router=$registry->get('lubed_router_router');
        if (!$this->router) {
            AppExceptions::dispatchFailed('NOT FOUND ROUTER', [
                'method'=>__METHOD__
            ]);
        }
    }

    public function dispatch(Request &$request) {
        $method=$request->getMethod();
        $uri=$request->getUri();
        $path=$uri->getPath();
        $key=sprintf('%s %s', $method, $path);
        $callee=$this->router->routing($key);
        if (!$callee) {
            AppExceptions::dispatchFailed(sprintf('Routing Failed:INVALID PATH %s', $path), [
                'method'=>__METHOD__
            ]);
        }
        $request->setRouted(true);
        return $callee;
    }
}
