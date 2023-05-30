<?php
namespace Lubed\HttpApplication;

use Error;
use Exception;
use Closure;
use Lubed\Supports\Starter;
use Lubed\Utils\Config;
use Lubed\Http\Streams\InputStream;
use Lubed\Http\Request as HttpRequest;
use Lubed\Http\Uri;
use Lubed\Exceptions\DefaultStarter as ExceptionStarter;
use Lubed\Exceptions\ExceptionResult;

final class DefaultStarter implements Starter
{
    private $app;
    private $config;

    public function __construct(array $params)
    {
        $this->config = new Config($params);
        $this->init();
    }

    public function start()
    {
        return $this->app->run();
    }

    private function init() {
        $this->registerExceptions();
        $router=$this->registerRouter();
        $this->initApplication();

        if (!$this->app)
        {
           AppExceptions::startFailed('http application initial failed',[
            'class'=>__CLASS__,
            'method'=>__METHOD__
           ]); 
        }
        $this->app->setRouter($router);
    }

    private function initApplication()
    {
        $kernel_class = $this->config->get('kernel');
        $kernel = new $kernel_class();
        $this->app = new HttpApplication($kernel);
        $this->app->init();
    }

    private function registerExceptions()
    {
        $config = $this->config->get('exception_capturer',true);
        (new ExceptionStarter($config,$this->exceptionRender()))->start();
    }

    private function exceptionRender():Closure
    {
        return function(ExceptionResult $result){
            $json = json_encode($result->getResult());
            $json_lines = explode(',',$json);
            //TODO:TODO:TODO
echo "\n",implode(',<br/>',$json_lines),"\n";
        };
    }

    private function registerRouter()
    {
        $router_config = $this->config->get('router');
        if(NULL===$router_config){
            return NULL;
        }
        $parameters = $router_config->get('parameters',true);
        $starter_class = $router_config->get('class');
        $instance = null;
        if($starter_class&& false !==class_exists($starter_class)){
            $instance = new $starter_class(...$parameters);
        }
        if($instance && $instance instanceOf Starter){
            $instance->start();
            return $instance->getInstance();
        }
        return NULL;
    }
}
