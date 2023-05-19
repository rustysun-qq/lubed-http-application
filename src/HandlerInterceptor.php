<?php
namespace Lubed\HttpApplication;

interface HandlerInterceptor
{
    public function preHandle(Action $action, $handler);
    public function postHandle(Action $action, $handler);
}