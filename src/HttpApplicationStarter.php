<?php
namespace Lubed\HttpApplication;

use Closure;
use Lubed\Container\DefaultContainer;
use Lubed\Exceptions\DefaultStarter as ExceptionStarter;
use Lubed\Exceptions\ExceptionResult;
use Lubed\Http\Streams\InputStream;
use Lubed\Http\Uri;
use Lubed\Supports\Starter;
use Lubed\Utils\Config;

final class HttpApplicationStarter implements Starter
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

    private function exceptionRender():Closure
    {
        return function(ExceptionResult $result) {
            $json = json_encode($result->getResult());
            $json_lines = explode(',',$json);
//TODO:
echo "\n",implode(',<br/>',$json_lines),"\n";
        };
    }

    private function init() {
        $this->registerExceptions();
        $this->initApplication();

        if (!$this->app)
        {
           AppExceptions::startFailed('http application initial failed',[
            'method'=>__METHOD__
           ]); 
        }
    }

    private function initApplication()
    {
        $this->app = new HttpApplication($this->config);
        $this->app->init();
    }

    private function registerExceptions()
    {
        $config = $this->config->get('exception_capturer',true);
        (new ExceptionStarter($config,$this->exceptionRender()))->start();
    }
}
