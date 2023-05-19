<?php
namespace Lubed\HttpApplication;

use Lubed\Supports\Starter;
use Lubed\Utils\Config;

final class DefaultStarter implements Starter
{
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function start()
    {
        $application = new HttpApplication($this->config);
        return $application->run();
    }

    private function init()
    {
        //TODO:get http request to application???
        $this->config = new Config($this->params);
    }
}