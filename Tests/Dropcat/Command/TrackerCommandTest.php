<?php

namespace Dropcat\tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Dropcat\Lib\DropcatFactories;

/**
 *
 */
class TrackerCommandTest extends \PHPUnit_Framework_TestCase
{


      /** @var Configuration */
    private $conf;
    /** @var  CommandTester */
    private $tester;
    private $mysqlMock;
    /** @var  ContainerBuilder */
    private $container;
    /** @var  DropcatFactories */
    private $factories_mock;
    /** @var  Application */
    private $application;
    private $mock;

  /**
   *
   */
    public function setUp()
    {
        // Building the container!
        $this->container = new ContainerBuilder();

        // Setting DropcatContainer to the DI-container we use.
        // This way, it will be available to the command.
        $this->container->set('DropcatContainer', $this->container);

        $this->conf = $configuration = new Configuration();

        $this->application = new Application();

        // We mock the command so that we later on can test Process.
        $this->mock = $this->getMockBuilder('Dropcat\Command\TrackerCommand')
        ->setConstructorArgs(array($this->container, $this->conf));

        $this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
    }

  /**
   *
   */
    public function testTrackerCommand()
    {
      
    }
}
