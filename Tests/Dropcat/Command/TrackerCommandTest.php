<?php
/**
 * Created by PhpStorm.
 * User: mikke
 * Date: 2017-04-07
 * Time: 11:04
 */

namespace Dropcat\tests;

use Dropcat\Command\TrackerCommand;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dropcat\Lib\DropcatFactories;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;




class TrackerCommandTest extends \PHPUnit_Framework_TestCase {
  public function setUp()
  {
	// building the container!
	$this->container = new ContainerBuilder();

	// Setting DropcatContainer to the DI-container we use.
	// This way, it will be available to the command.
	$this->container->set('DropcatContainer', $this->container);

	$this->conf = $configuration = new Configuration();

	$this->application = new Application();

	// We mock the command so that we later on can test Process.
	$this->mock = $this->getMockBuilder('Dropcat\Command\RunLocalCommand')
	  ->setConstructorArgs(array($this->container, $this->conf));

	$this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
  }
}
