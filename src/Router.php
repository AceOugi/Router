<?php

namespace AceOugi;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Router
{
    /** @var array */
    protected $map = []; //TODO: import/export

    /**
     * @param string $methods
     * @param string $pattern
     * @param callable[] ...$callables
     */
    public function map(string $methods, string $pattern, ...$callables)
    {
        foreach (explode('|', $methods) as $method)
            $this->map[$method][] = ['pattern' => $pattern, 'callables' => $callables];
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     */
    public function any(string $pattern, ...$callables)
    {
        $this->map('GET|POST|PUT|PATCH|DELETE', $pattern, ...$callables);
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     */
    public function duo(string $pattern, ...$callables)
    {
        $this->map('GET|POST', $pattern, ...$callables);
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     */
    public function get(string $pattern, ...$callables)
    {
        $this->map('GET', $pattern, ...$callables);
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     */
    public function post(string $pattern, ...$callables)
    {
        $this->map('POST', $pattern, ...$callables);
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     */
    public function put(string $pattern, ...$callables)
    {
        $this->map('PUT', $pattern, ...$callables);
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     */
    public function patch(string $pattern, ...$callables)
    {
        $this->map('PATCH', $pattern, ...$callables);
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     */
    public function delete(string $pattern, ...$callables)
    {
        $this->map('DELETE', $pattern, ...$callables);
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     * @return Router
     */
    public function group(string $pattern, ...$callables) : Router
    {
        $router = new Router();
        $callables[] = $router;

        $this->any($pattern, ...$callables);

        return $router;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $path = trim(preg_replace('{/{2,}}', '/', urldecode($request->getUri()->getPath())), '/');

        foreach ($this->map[$request->getMethod()] ?? [] as $route)
            if (preg_match('{^'.$route['pattern'].'$}i', $path, $attributes))
            {
                foreach ($attributes as $attribute_key => $attribute_value)
                    $request = $request->withAttribute($attribute_key, $attribute_value);

                return $next($request, $response, ...$route['callables']);
            }

        return $next($request, $response);
    }
}
