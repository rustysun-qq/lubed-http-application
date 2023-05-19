<?php
namespace Lubed\HttpApplication;

use Lubed\Logger\LoggerAware;
use Lubed\Reflection\ReflectionFactory;
use Lubed\Reflection\ReflectionFactoryAware;
use Lubed\Http\Request;

use Logger;
use ReflectionException;
//TODO:???
class DefaultDispatcher implements LoggerAware, ReflectionFactoryAware
{
    private $logger;
    protected $reflection_factory;
    private $interceptors=[];
    private $dispatcher_info;

    public function dispatch(Request $request) {
        $this->init($request);
        $dispatcher_info =$this->dispatcher_info;
        $controller=$this->dispatcher_info->handler;
        $action_handler=$this->dispatcher_info->method;
        $action=$this->dispatcher_info->action;
        if (!method_exists($controller, $action_handler)) {
            //TODO:???
            throw new MvcException('No valid action handler found: ' . $action_handler);
        }
        $interceptors=$this->dispatcher_info->interceptors;
        $filtersPassed=true;
        $result=false;
        foreach ($interceptors as $interceptor) {
            $this->logger->debug("Running pre filter: " . get_class($interceptor));
            $result=$interceptor->preHandle($action, $controller);
            //TODO:???
            if ($result instanceof ModelAndView) {
                return $result;
            }
            if ($result === false) {
                $filtersPassed=false;
                $this->logger->debug("Filter returned false, stopping dispatch");
                break;
            }
        }
        if ($filtersPassed) {
            $result=$this->invokeAction($controller, $actionHandler, $action->getArguments());
            foreach ($interceptors as $interceptor) {
                $this->logger->debug("Running post filter: " . get_class($interceptor));
                $interceptor->postHandle($action, $controller);
            }
        }
        return $result;
    }

    public function setReflectionFactory(ReflectionFactory $reflectionFactory) {
        $this->reflectionFactory=$reflectionFactory;
    }

    public function setLogger(Logger $logger) {
        $this->logger=$logger;
    }

    
    private function init(Request $request)
    {
        //TODO:init dispatcher info
        // $this->dispatcher_info = DispatchInfo $dispatchInfo;
    }

    private function invokeAction($object, string $method, array $arguments) {
        $methodInfo=$this->reflection_factory->getMethod(get_class($object), $method);
        $parameters=$methodInfo->getParameters();
        $values=[];
        $total=count($parameters);
        for ($i=0; $i < $total; $i++) {
            $parameter=array_shift($parameters);
            $name=$parameter->getName();
            if (isset($arguments[$name])) {
                $values[]=$arguments[$name];
            } else if ($parameter->isOptional()) {
                $values[]=$parameter->getDefaultValue();
            } else {
                $ctl=get_class($object);
                //TODO:???
                throw new MvcException("Missing required argument: $name for action $ctl:$method");
            }
        }
        return $methodInfo->invokeArgs($object, $values);
    }
}
