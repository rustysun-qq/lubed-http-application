<?php
namespace Lubed\HttpApplication;

use Lubed\Http\RedirectResponse;

class Redirector
{
    /**
     * The application instance.
     *
     * @var HttpApplication
     */
    protected $app;

    /**
     * Create a new redirector instance.
     *
     * @param  HttpApplication  $app
     * @return void
     */
    public function __construct(HttpApplication $app)
    {
        $this->app = $app;
    }

    /**
     * Create a new redirect response to the given path.
     *
     * @param  string  $path
     * @param  int     $status
     * @param  array   $headers
     * @param  bool    $secure
     * @return RedirectResponse
     */
    public function to($path, $status = 302, $headers = [], $secure = null)
    {
        $path = $this->app->make('url')->to($path, [], $secure);

        return $this->createRedirect($path, $status, $headers);
    }

    /**
     * Create a new redirect response to a named route.
     *
     * @param  string  $route
     * @param  array   $parameters
     * @param  int     $status
     * @param  array   $headers
     * @return RedirectResponse
     */
    public function route($route, $parameters = [], $status = 302, $headers = [])
    {
        $path = $this->app->make('url')->route($route, $parameters);

        return $this->to($path, $status, $headers);
    }

    /**
     * Create a new redirect response.
     *
     * @param  string  $path
     * @param  int     $status
     * @param  array   $headers
     * @return RedirectResponse
     */
    protected function createRedirect($path, $status, $headers)
    {
        $redirect = new RedirectResponse($path, $status, $headers);

        $redirect->setRequest($this->app->make('request'));

        return $redirect;
    }
}
