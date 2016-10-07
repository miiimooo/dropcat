<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2016-10-07
 * Time: 12:07
 */

namespace Dropcat\Lib;

use phpseclib\Net\SSH2;

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
        $class = $this->container->getParameter('factory.libs.ssh');
        return new $class($server, $ssh_port);
    }
}
