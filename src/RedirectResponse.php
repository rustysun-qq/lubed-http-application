<?php
namespace Lubed\HttpApplication;

use Lubed\Http\Response;
use Lubed\Http\Request;


class RedirectResponse extends Response{
    protected $targetUrl;


    /**
     * Creates a redirect response so that it conforms to the rules defined for a redirect status code.
     *
     * @param string $url     The URL to redirect to. The URL should be a full URL, with schema etc.,
     *                        but practically every browser redirects on paths only as well
     * @param int    $status  The status code (302 by default)
     * @param array  $headers The headers (Location is always set to the given URL)
     *
     * @throws \InvalidArgumentException
     *
     * @see https://tools.ietf.org/html/rfc2616#section-10.3
     */
    public function __construct(string $url, int $status = 302, array $headers = [])
    {
        $reason = NULL;
        parent::__construct('', $status,$reason, $headers);

        $this->setTargetUrl($url);

        if (!$this->isRedirect()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code is not a redirect ("%s" given).', $status));
        }

        if (301 == $status && !\array_key_exists('cache-control', array_change_key_case($headers, \CASE_LOWER))) {
            $this->withoutHeader('cache-control');
        }
    }

    /**
     * Factory method for chainability.
     *
     * @param string $url     The url to redirect to
     * @param int    $status  The response status code
     * @param array  $headers An array of response headers
     *
     * @return static
     */
    public static function create($url = '', $status = 302, $headers = [])
    {
        return new static($url, $status, $headers);
    }

    public function isRedirect(string $location = null): bool
    {
        return \in_array($this->getStatusCode(), [201, 301, 302, 303, 307, 308]) && (null === $location ?: $location == $this->getHeader('Location'));
    }

    /**
     * Returns the target URL.
     *
     * @return string target URL
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * Sets the redirect target of this response.
     *
     * @param string $url The URL to redirect to
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setTargetUrl($url)
    {
        if ('' === ($url ?? '')) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $this->targetUrl = $url;

        $this->setContent(
            sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url=\'%1$s\'" />

        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));

        $this->withAddedHeader('Location', $url);

        return $this;
    }

    /**
     * The request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * Add multiple cookies to the response.
     *
     * @param  array  $cookies
     * @return $this
     */
    public function withCookies(array $cookies)
    {

        $this->withCookies($cookies);
        return $this;
    }

    /**
     * Get the request instance.
     *
     * @return Request|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the request instance.
     *
     * @param  Request  $request
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
}