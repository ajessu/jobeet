<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Matcher;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * UrlMatcher matches URL based on a set of routes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class UrlMatcher implements UrlMatcherInterface
{
    protected $routes;
    protected $defaults;
    protected $context;

    /**
     * Constructor.
     *
     * @param RouteCollection $routes   A RouteCollection instance
     * @param array           $context  The context
     * @param array           $defaults The default values
     */
    public function __construct(RouteCollection $routes, array $context = array(), array $defaults = array())
    {
        $this->routes = $routes;
        $this->context = $context;
        $this->defaults = $defaults;
    }

    /**
     * Sets the request context.
     *
     * @param array $context  The context
     */
    public function setContext(array $context = array())
    {
        $this->context = $context;
    }

    /**
     * Tries to match a URL with a set of routes.
     *
     * Returns false if no route matches the URL.
     *
     * @param  string $pathinfo The path info to be parsed
     *
     * @return array|false An array of parameters or false if no route matches
     */
    public function match($pathinfo)
    {
        foreach ($this->routes->all() as $name => $route) {
            $compiledRoute = $route->compile();

            // check HTTP method requirement

            if (isset($this->context['method']) && (($req = $route->getRequirement('_method')) && !preg_match(sprintf('#^(%s)$#xi', $req), $this->context['method']))) {
                continue;
            }

            // check the static prefix of the URL first. Only use the more expensive preg_match when it matches
            if ('' !== $compiledRoute->getStaticPrefix() && 0 !== strpos($pathinfo, $compiledRoute->getStaticPrefix())) {
                continue;
            }

            if (!preg_match($compiledRoute->getRegex(), $pathinfo, $matches)) {
                continue;
            }

            return array_merge($this->mergeDefaults($matches, $route->getDefaults()), array('_route' => $name));
        }

        return false;
    }

    protected function mergeDefaults($params, $defaults)
    {
        $parameters = array_merge($this->defaults, $defaults);
        foreach ($params as $key => $value) {
            if (!is_int($key)) {
                $parameters[$key] = urldecode($value);
            }
        }

        return $parameters;
    }
}
