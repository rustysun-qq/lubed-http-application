<?php
namespace Lubed\HttpApplication;

use Closure;
use Exception;
use Lubed\Exceptions\DefaultStarter as ExceptionStarter;
use Lubed\Exceptions\ExceptionResult;
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

    private function exceptionRender(&$app):Closure
    {
        return function(ExceptionResult $result) use ($app) {
            $app_config = $app->getConfig();
            $exception_config = $app_config->get('exception_capturer');
            $render_config = $exception_config->get('render');
            $view_class = $render_config->get('class');
            $path = $render_config->get('path');
            $suffix = $render_config->get('suffix');
            $view_name = $render_config->get('view_name');
            $data = $result->getResult();
            $data['tables']=  [
                "GET Data"              => $_GET,
                "POST Data"             => $_POST,
                "Files"                 => isset($_FILES) ? $_FILES: [],
                "Cookies"               => $_COOKIE,
                "Session"               => isset($_SESSION) ? $_SESSION :  [],
                "Server/Request Data"   => $_SERVER,
                "Environment Variables" => $_ENV,
            ];
            $view = new $view_class($path,$data,$suffix);
            $view->load($view_name);
            exit($view->render());
        };
    }

    private function init() {
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
        $this->registerExceptions();
        $this->app->init();
    }

    private function registerExceptions()
    {
        $config = $this->config->get('exception_capturer',true);
        (new ExceptionStarter($config,$this->exceptionRender($this->app)))->start();
    }
}
