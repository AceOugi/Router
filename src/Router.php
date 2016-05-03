<?php

namespace AceOugi;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Router
{
    /** @var array */
    protected $map = []; // ['pattern', 'callables', 'method', ...]

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     * @return RouterSpecifier
     */
    public function map(string $pattern, ...$callables) : RouterSpecifier
    {
        $route = ['pattern' => $pattern, 'callables' => $callables];
        return new RouterSpecifier($this->map[] =&$route);
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     * @return RouterSpecifier
     */
    public function any(string $pattern, ...$callables) : RouterSpecifier
    {
        return $this->map($pattern, ...$callables)->setMethod('GET|POST|PUT|PATCH|DELETE|OPTIONS');
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     * @return RouterSpecifier
     */
    public function get(string $pattern, ...$callables) : RouterSpecifier
    {
        return $this->map($pattern, ...$callables)->setMethod('GET');
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     * @return RouterSpecifier
     */
    public function post(string $pattern, ...$callables) : RouterSpecifier
    {
        return $this->map($pattern, ...$callables)->setMethod('POST');
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     * @return RouterSpecifier
     */
    public function put(string $pattern, ...$callables) : RouterSpecifier
    {
        return $this->map($pattern, ...$callables)->setMethod('PUT');
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     * @return RouterSpecifier
     */
    public function patch(string $pattern, ...$callables) : RouterSpecifier
    {
        return $this->map($pattern, ...$callables)->setMethod('PATCH');
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     * @return RouterSpecifier
     */
    public function delete(string $pattern, ...$callables) : RouterSpecifier
    {
        return $this->map($pattern, ...$callables)->setMethod('DELETE');
    }

    /**
     * @param string $pattern
     * @param callable[] ...$callables
     * @return RouterSpecifier
     */
    public function options(string $pattern, ...$callables) : RouterSpecifier
    {
        return $this->map($pattern, ...$callables)->setMethod('OPTIONS');
    }

    /**
     * @todo add submap system (THIS SYSTEM IS A DEMO, DONT USE IT)
     * @param string $pattern
     * @param callable[] ...$callables
     * @return Router
     */
    public function group(string $pattern, ...$callables) : Router
    {
        $map = new Router();
        $callables[] = $map;

        $this->map($pattern, ...$callables);

        return $map;
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

        foreach ($this->map as $route)
            if (preg_match('{^'.$route['pattern'].'$}i', $path, $attributes))
            {
                //TODO: add option handler checker :: array_filter($attributes, 'is_string', ARRAY_FILTER_USE_KEY)
                foreach ($attributes as $attribute_key => $attribute_value)
                    $request = $request->withAttribute($attribute_key, $attribute_value);

                return $next($request, $response, ...$route['callables']);
            }

        return $next($request, $response);
    }
}
