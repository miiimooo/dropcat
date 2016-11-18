<?php


namespace Dropcat\tests;

use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InitCommandTest extends \PHPUnit_Framework_TestCase
{

    private $conf;
    private $application;
    private $commandMock;

    private $container;
    private $mock;

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
        $this->mock = $this->getMockBuilder('\Dropcat\Command\InitCommand')
            ->setConstructorArgs(array($this->container, $this->conf));
    }

    public function testInitCommand()
    {
        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $command_mock->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo("drush @mysite entup -y"))
            ->willReturn($process_mock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'entity-update',
                '-d'      => 'mysite',
            )
        );
    }

    public function testEntityUpdateFailure()
    {
        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(false);

        $this->expectException('\Symfony\Component\Process\Exception\ProcessFailedException');
        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $command_mock->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo("drush @mysite entup -y"))
            ->willReturn($process_mock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'entity-update',
                '-d'      => 'mysite',
            ),
            array(
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE
            )
        );
    }
}

