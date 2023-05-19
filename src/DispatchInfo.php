<?php
namespace Lubed\HttpApplication;

final class DispatchInfo {
 
    private $action;

    private $handler;

    private $method;

    private $interceptors;

 
    public function __construct(Action $action, $handler, $method, array $interceptors=[]) {
        $this->action=$action;
        $this->handler=$handler;
        $this->method=$method;
        $this->interceptors=$interceptors;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getInterceptors()
    {
        return $this->interceptors;
    }
}
