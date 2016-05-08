<?php

namespace AceOugi;

class Router
{
    /** @var array */
    protected $map = [];

    /**
     * Router constructor.
     * @param array|null $map
     */
    public function __construct(array $map = null)
    {
        if ($map)
            $this->import($map);
    }

    /**
     * @return array
     */
    public function export() : array
    {
        return $this->map;
    }

    /**
     * @param array $map
     * @return self
     */
    public function import(array $map)
    {
        $this->map = $map;

        return $this;
    }

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
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return callable[]
     */
    public function match(\Psr\Http\Message\ServerRequestInterface &$request)
    {
        $path = ltrim(preg_replace('{/{2,}}', '/', urldecode($request->getUri()->getPath())), '/');

        foreach ($this->map[$request->getMethod()] ?? [] as $route)
            if (preg_match('{^'.$route['pattern'].'$}i', $path, $attributes))
            {
                foreach ($attributes as $attribute_key => $attribute_value)
                    $request = $request->withAttribute($attribute_key, $attribute_value);

                return $route['callables'];
            }

        return [];
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable $next
     * @return mixed
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, callable $next = null)
    {
        return $next($request, $response, ...$this->match($request));
    }
}
