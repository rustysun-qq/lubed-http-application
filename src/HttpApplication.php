<?php
namespace Lubed\HttpApplication;
use Lubed\Utils\Config;

final class HttpApplication
{
    private $config;
    public function __construct(Config $config)
    {
        $this->init($config);
    }

    public function run()
    {
        $dispatcher = new DefaultDipatcher();
        $dispatcher->dispatch($this->config->get('request'));
    }

    private function init(Config $config)
    {
        $this->config = $config;
    }
}