<?php
namespace Lubed\HttpApplication;

interface Route {
    public function route(& $request);
}

