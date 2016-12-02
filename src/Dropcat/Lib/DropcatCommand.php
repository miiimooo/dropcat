<?php
namespace Dropcat\Lib;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

/**
 * Class DropcatCommand
 *
 * This class collects the construct and runProcess which should share
 * a common structure for our commands.
 *
 * __construct, here, makse sure we get the container injected as well as
 * receive the configuration.
 *
 * The method runProcess is there so we can more easily test running a process
 * by mocking the method when testing.
 *
 * @package Dropcat\Lib
 */
class DropcatCommand extends Command
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;
    /**
     * @var \Dropcat\Services\Configuration
     */
    protected $configuration;

    public function __construct(ContainerBuilder $container, Configuration $conf)
    {
        $this->configuration = $conf;
        parent::__construct();
        $this->container = $container;
    }

    protected function runProcess($command)
    {
        return new Process($command);
    }

    protected function exitCommand($exitCode = 1)
    {
        exit($exitCode);
    }
}