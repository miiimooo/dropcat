<?php
namespace Dropcat\Lib;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;

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
}