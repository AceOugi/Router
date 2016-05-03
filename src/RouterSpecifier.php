<?php

namespace AceOugi;

class RouterSpecifier
{
    /** @var array */
    protected $route;

    /**
     * RouterSpecifier constructor.
     * @param array $route
     */
    public function __construct(array &$route)
    {
        $this->route =&$route;
    }

    /**
     * @param string $scheme
     * @return self
     */
    public function setScheme(string $scheme) : self
    {
        $this->route['scheme'] = $scheme;
        return $this;
    }

    /**
     * @param string $method
     * @return self
     */
    public function setMethod(string $method) : self
    {
        $this->route['method'] = $method;
        return $this;
    }
}
