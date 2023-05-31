<?php
namespace Lubed\HttpApplication;

use Lubed\Http\Request;

final class HttpRequest extends Request
{
    private bool $is_routed=FALSE;

    public function isRouted():bool
    {
        return $this->is_routed;
    }

    public function setRouted(bool $is_routed)
    {
        $this->is_routed=$is_routed;
    }
}