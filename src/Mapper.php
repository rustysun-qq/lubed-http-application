<?php
namespace Lubed\HttpApplication;
//TODO：？？？
interface Mapper {

    public function map(Action $action);

    public function setMap(array $map);
}