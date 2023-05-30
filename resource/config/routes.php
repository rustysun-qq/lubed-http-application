<?php
//TODO: router configurator
if (isset($router)) {
    $router->get('/hello', '\\App\\Http\\Controllers\\HelloController@hello');
}