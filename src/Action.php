<?php
namespace Lubed\HttpApplication;

 interface Action
 {
    public function getId();
    
    public function getArguments();
}
