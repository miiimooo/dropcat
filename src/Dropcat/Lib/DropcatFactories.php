<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2016-10-07
 * Time: 12:07
 */

namespace Dropcat\Lib;

/**
 * Class DropcatFactories
 *
 * Silly little factory class, probably a bad way of solving this but for the
 * moment, this is how we solve the fact that we sometimes need dynamic
 * parameters when instantiating non-shared services.
 *
 * There is probably a good symfony-way of doing this, but right now, I cannot
 * find that solution.
 * @package Dropcat\Lib
 */
class DropcatFactories
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function ssh($server, $ssh_port)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function symfonystyle()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function splfileobject()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function __call($name, $args) {
        $name = strtolower($name);
        $class = new \ReflectionClass($this->container->getParameter('factory.libs.' . $name));
        return $class->newInstanceArgs($args);
    }
}
