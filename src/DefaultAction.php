<?php
namespace Lubed\HttpApplication;

use Lubed\Supports\Action;

class DefaultAction implements Action{
    private $id;
    private $method;
    private $arguments;

    private function __construct($id, string $method,array $arguments=[]) {
        $this->id=$id;
        $this->method = $method;
        $this->arguments=$arguments;
    }

    public function getId() {
        return $this->id;
    }

    public function getMethod(){
        return $this->method;
    }

    public function getArguments() {
        return $this->arguments;
    }
}
