<?php

namespace Ace;

class Router
{
    /** @var array */
    protected $map = [];

    /** @var callable */
    protected $resolver;

    /**
     * Router constructor.
     * @param callable|null $resolver
     * @param array[] ...$maps
     */
    public function __construct(callable $resolver = null, array ...$maps)
    {
        foreach ($maps as $map)
            $this->import($map);

        $this->resolver = $resolver;
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
        foreach ($map as $key => $data)
            switch (is_numeric($key) ? 'new' : $key)
            {
                case 'GET'   : $this->map['GET'] = array_merge($this->map['GET'] ?? [], $data); break;
                case 'POST'  : $this->map['POST'] = array_merge($this->map['POST'] ?? [], $data); break;
                case 'PUT'   : $this->map['PUT'] = array_merge($this->map['PUT'] ?? [], $data); break;
                case 'PATCH' : $this->map['PATCH'] = array_merge($this->map['PATCH'] ?? [], $data); break;
                case 'DELETE': $this->map['DELETE'] = array_merge($this->map['DELETE'] ?? [], $data); break;
                default: $this->map($data['method'], $data['pattern'], ...$data['callables']); break;
            }

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

    protected function resolve()
    {
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
     * @param callable|null $next
     * @return mixed
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, callable $next = null)
    {
        $callables = $this->match($request);

        return $next($request, $response, ...$callables);
    }
}
