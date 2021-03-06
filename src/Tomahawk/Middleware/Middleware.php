<?php

namespace Tomahawk\Middleware;

use Tomahawk\DI\ContainerAwareInterface;
use Tomahawk\DI\ContainerInterface;

abstract class Middleware implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Boot the middleware
     *
     * @return mixed
     */
    abstract public function boot();


    /**
     * Returns the middleware name (the class short name).
     *
     * @return string The Middleware name
     *
     * @api
     */
    final public function getName()
    {
        if (null !== $this->name) {
            return $this->name;
        }

        $name = get_class($this);
        $pos = strrpos($name, '\\');

        return $this->name = false === $pos ? $name : substr($name, $pos + 1);
    }
}
