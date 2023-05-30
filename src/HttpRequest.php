<?php
namespace Lubed\HttpApplication;

use Lubed\Http\Request;

final class HttpRequest extends Request
{
    use \Lubed\Router\RoutingRequestTrait;
}