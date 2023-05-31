<?php
namespace Lubed\HttpApplication;
use Lubed\Http\Streams\InputStream;
use Lubed\Utils\Config;
use Lubed\Supports\Application;
use Lubed\Supports\Kernel;
use Lubed\Http\Response as HttpResponse;
use Lubed\Http\Uri;
use Lubed\Reflections\DefaultReflectionFactory;

final class HttpApplication implements Application
{
    private $request;
    private $has_run;
    private $methods;
    private $kernel;
    //boot providers
    private $booted;
    private $providers;
    private $router;

    public function __construct(Kernel $kernel,$router)
    {
        $this->methods = [];
        $this->kernel = $kernel;
        $this->router = $router;
    }

    public function init()
    {
        $this->request = $this->initRequestInstance();
    }

    public function setRequest(HttpRequest $request, array $options)
    {
        $this->request = $request;
        $this->request_options = $options;
    }

    public function getRequest()
    {
        return $this->request;
    }


    public function getRouter():Router
    {
        return $this->router;
    }

    public function getOptions()
    {
        return $this->request_options;
    }

    public function getKernel()
    {
        return $this->kernel;
    }

    private function boot()
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            $this->bootProvider($provider);
        }

        $this->booted = true;
    }

    private function bootProvider($provider)
    {
        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }
    }

    public function run()
    {
        if (!$this->has_run) {
            $this->has_run = true;
        }

        $dispatcher = new DefaultDispatcher($this->router);
        $callee = $dispatcher->dispatch($this->request);//dispatch to router
        $this->kernel->init($callee,$this->request);
        $body=NULL;
        $this->kernel->boot($body);
        $response = new HttpResponse($body);
        $response->send();

die("\n-- dispatch ok--\n");
        return $this->has_run;
    }

    private function initRequestInstance()
    {
        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? '';
        $version = $protocol ? str_replace('HTTP/', '', $protocol) : '1.1';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $method = $method ? $method : 'GET';
        $uri = $this->initUriByServerEnv();
        //get headers
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        if (!$headers) {
            $headers=[];
            foreach ($_SERVER as $name=>$value) {
                if ('HTTP_'===substr($name, 0, 5)) {
                    $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name,5)))));
                    $headers[$header]=$value;
                }
                else{
                    setenv($name,$value);
                }
            }
        }
        //body
        $body=new InputStream();
        $request = new HttpRequest($method, $uri, $headers, $body, $version);

        return $request->withCookies($_COOKIE)
                       ->withParsedBody($_POST)
                       ->withQueryParameters($_GET)
                       ->withFiles($_FILES)
                       ->withServer($_SERVER);
    }

    private function initUriByServerEnv() {
        $uri=new Uri('');
        $env_https=$_SERVER['HTTPS'] ?? 'off';
        if ($env_https) {
            $uri=$uri->withScheme($env_https == 'on' ? 'https' : 'http');
        }
        $env_host=$_SERVER['HTTP_HOST'];
        $env_host=$env_host ? $env_host : $_SERVER['SERVER_NAME'];

        if ($env_host) {
            $host_info = explode(':',$env_host);
            $uri=$uri->withHost($host_info[0]??$env_host);
        }
        $env_port=$_SERVER['SERVER_PORT'];
        if ($env_port) {
            $uri=$uri->withPort($env_port);
        }
        $env_uri=$_SERVER['REQUEST_URI'];
        if ($env_uri) {
            $uri=$uri->withOriginalUri($env_uri);
        }
        $path_info=$_SERVER['PATH_INFO']??NULL;
        $path=$path_info ? $path_info : $env_uri;
        if ($path) {
            $path=current(explode('?', $path));
            $uri=$uri->withPath($path);
        }

        //TODO:remove默认格式
        $format='json';

        if ($path && false !== strpos('.', $format)) {
            $path_info=explode('.', $path);
            $format=$path_info && is_array($path_info) ? array_pop($path_info) : $format;
        }

        $this->format=strtolower($format);
        $env_query=$_SERVER['QUERY_STRING']??'';
        if ($env_query) {
            $uri=$uri->withQuery($env_query);
        }
        return $uri;
    }
}
