<?php
//TODO:
error_reporting(E_ALL);
ini_set('display_errors', true);
date_default_timezone_set('UTC');
ini_set('set_time_limit', 0);

//自动载入
$root_path=dirname(__DIR__);
$autoloadFile=sprintf('%s/vendor/autoload.php',$root_path);

if (!file_exists($autoloadFile))
{
    echo '{"code":9999,"msg":"please run command \"composer update\" first!"}', "\n";
    exit(1);
}

require_once($autoloadFile);

use Lubed\HttpApplication\DefaultStarter as HTTPApplicationStarter;

//config(TODO:AUTO CONFIGURATOR WILL BY COMPOSER "discovery" COMPONENT)
$config_data = [
    'root_path' => $root_path,
    //MVC kernel
    'kernel' => '\\Lubed\\MVCKernel\\DefaultKernel',
    //HTTP REQUEST ROUTER
    'router' =>[
        'class'=> '\\Lubed\\Router\\DefaultStarter',
        'parameters'=>[sprintf('%s/resource/config/routes.php',$root_path)],
    ]
];

$starter = new HTTPApplicationStarter($config_data);
$starter->start();
 