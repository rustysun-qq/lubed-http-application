<?php
namespace Lubed\HttpApplication;

class DefaultAction implements Action{
    private $id;
    private $arguments;

    private function __construct($id, array $arguments=[]) {
        $this->id=$id;
        $this->arguments=$arguments;
    }

    public function getId() {
        return $this->id;
    }

    public function getArguments() {
        return $this->arguments;
    }
}